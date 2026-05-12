<?php
// api/approve_enrollment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID is missing.']);
    exit();
}

// Set your standard initial enrollment fee here (e.g., 5000 pesos)
$tuition_amount = 5000.00; 

try {
    // 1. Approving the student means stamping today's date as their enrollment date
    $stmt = $pdo->prepare("UPDATE students SET enrollment_date = CURDATE() WHERE student_id = :id");
    $stmt->execute(['id' => $data->student_id]);

    // ==========================================
    // FINANCE SYNC: AUTOMATIC TUITION COLLECTION
    // ==========================================
    
    // 2. Automatically generate the receipt in the Student Payments table
    $payStmt = $pdo->prepare("
        INSERT INTO student_payments (student_id, amount, payment_date, description, status) 
        VALUES (:student_id, :amount, NOW(), 'Enrollment Approved - Initial Tuition Paid', 'Paid')
    ");
    $payStmt->execute([
        'student_id' => $data->student_id,
        'amount'     => $tuition_amount
    ]);

    // 3. Log the income in the Master Ledger (Assuming account_id 1 is your general Cash/Bank account)
    $ledgerStmt = $pdo->prepare("
        INSERT INTO transactions (trans_date, account_id, description, amount, type) 
        VALUES (NOW(), 1, CONCAT('Enrollment Tuition Collection - Student ID: ', :student_id), :amount, 'Credit')
    ");
    $ledgerStmt->execute([
        'student_id' => $data->student_id,
        'amount'     => $tuition_amount
    ]);

    echo json_encode(['success' => true, 'message' => 'Student approved and tuition payment automatically synced to Finance!']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
