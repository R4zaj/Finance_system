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
// ACTION 2: POST APPROVAL TO LMS
// ---------------------------------------------------------
if ($action === 'approve') {
    $data = json_decode(file_get_contents("php://input"));
    
    // Using student_id based on your JSON structure
    if (empty($data->student_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing Student ID.']);
        exit();
    }

    // Prepare standard Form POST data for the external LMS
    $postData = http_build_query([
        'student_id' => $data->student_id,
        'status'     => 'Approved'
    ]);

    // Send the POST request to the action expected by the LMS
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

    if ($httpCode === 200 && $response) {
        echo $response;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'LMS Update Failed. HTTP: ' . $httpCode]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
?>
