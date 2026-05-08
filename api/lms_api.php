<?php
// api/lms_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

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
// ACTION 2: POST APPROVAL TO LMS (UPDATED FOR ENROLLMENT_ID)
// ---------------------------------------------------------
if ($action === 'approve') {
    $data = json_decode(file_get_contents("php://input"));
    
    // We now look for an array of enrollment_ids instead of a single student_id
    if (empty($data->enrollment_ids) || !is_array($data->enrollment_ids)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing enrollment IDs.']);
        exit();
    }

    $successCount = 0;
    $lastError = "";

    // Loop through every class the student is enrolling in and approve it
    foreach ($data->enrollment_ids as $eid) {
        // We use the exact parameter name the LMS asked for: 'enrollment_id'
        $postData = http_build_query([
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
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $successCount++;
        } else {
            $lastError = strip_tags($response);
        }
    }

    if ($successCount > 0) {
        echo json_encode(['status' => 'success', 'message' => "$successCount subjects approved."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "LMS rejected the approval. Details: " . $lastError]);
    }
    exit();
}
