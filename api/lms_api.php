<?php
// api/lms_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// 1. Connect to the local Finance Database
require_once '../includes/db.php'; 

$action = isset($_GET['action']) ? $_GET['action'] : '';

$baseUrl = "https://artisanslms.onrender.com/backend/api/export_tuition.php";
$apiKey = "fN3kzPqLmW8xRtYcJ2sDhUeVbA7gXo1Q";

// ---------------------------------------------------------
// ACTION 1: FETCH PENDING ENROLLMENTS
// ---------------------------------------------------------
if ($action === 'get_pending') {
    $url = $baseUrl . "?action=get_pending&api_key=" . $apiKey;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        echo $response;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to connect to LMS API.']);
    }
    exit();
}

// ---------------------------------------------------------
// ACTION 2: POST APPROVAL TO LMS & SYNC FINANCE
// ---------------------------------------------------------
if ($action === 'approve') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->enrollment_ids) || !is_array($data->enrollment_ids)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing enrollment IDs.']);
        exit();
    }

    // Extract payment info and student name from the UI request
    $amount_paid = isset($data->amount) ? (float)$data->amount : 0.00;
    $student_id = isset($data->student_id) ? $data->student_id : null;
    $student_full_name = isset($data->student_name) ? $data->student_name : 'LMS Student';

    $successCount = 0;
    $lastError = "";

    // Loop through every subject the student is enrolling in
    foreach ($data->enrollment_ids as $eid) {
        $postData = json_encode([
            'enrollment_id' => $eid,
            'status'        => 'Approved'
        ]);

        $url = $baseUrl . "?action=approve&api_key=" . $apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $successCount++;
        } else {
            $lastError = strip_tags($response);
        }
    }

    // If LMS approval worked, handle local Finance recording
    if ($successCount > 0) {
        
        // FINANCE SYNC (AUTOMATIC TUITION)
        if ($amount_paid > 0) {
            try {
                $db_student_id = $student_id ? $student_id : 0; 

                // Disable Foreign Key checks temporarily
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

                // Check if the student exists locally. If not, create them!
                $checkStudent = $pdo->prepare("SELECT student_id FROM students WHERE student_id = :id");
                $checkStudent->execute(['id' => $db_student_id]);
                
                if (!$checkStudent->fetch()) {
                    // Split full name into first and last name for your table
                    $nameParts = explode(' ', $student_full_name, 2);
                    $fName = $nameParts[0];
                    $lName = isset($nameParts[1]) ? $nameParts[1] : '';

                    $regStudent = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, enrollment_date) VALUES (:id, :fname, :lname, NOW())");
                    $regStudent->execute([
                        'id' => $db_student_id,
                        'fname' => $fName,
                        'lname' => $lName
                    ]);
                }

                // A. Log the receipt
                $payStmt = $pdo->prepare("INSERT INTO student_payments (student_id, amount, pay_date, description) VALUES (:student_id, :amount, NOW(), 'Enrollment Approved')");
                $payStmt->execute(['student_id' => $db_student_id, 'amount' => $amount_paid]);

                // B. Log to Master Ledger
                $ledgerStmt = $pdo->prepare("INSERT INTO transactions (trans_date, account_id, description, amount, type) VALUES (NOW(), 1, CONCAT('Enrollment Tuition - ', :sname), :amount, 'Credit')");
                $ledgerStmt->execute(['sname' => $student_full_name, 'amount' => $amount_paid]);

                // Turn the security checks back on!
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            } catch (PDOException $e) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
                echo json_encode(['status' => 'success', 'message' => "Approved, but Finance Sync failed: " . $e->getMessage()]);
                exit();
            }
        }
        
        // If everything worked perfectly
        echo json_encode(['status' => 'success', 'message' => "$successCount subjects approved and Finance Ledger updated!"]);
    } else {
        // If the LMS rejected the approval entirely
        echo json_encode(['status' => 'error', 'message' => "LMS rejected the approval. Details: " . $lastError]);
    }
    exit();
}
?>
