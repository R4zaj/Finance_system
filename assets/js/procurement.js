<?php
// api/procurement_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Note: You might not even need db.php anymore if this file only talks to the external API!
require_once '../includes/db.php'; 

$action = isset($_GET['action']) ? $_GET['action'] : '';

// ---------------------------------------------------------
// ACTION 1: FETCH POs FROM EXTERNAL INVENTORY SYSTEM
// ---------------------------------------------------------
if ($action === 'get_pos') {
    $url = "https://icis-inventory.onrender.com/includes/api/api.php?action=get_pos";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        echo $response;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to connect to the Inventory API.']);
    }
    exit();
}

// ---------------------------------------------------------
// ACTION 2: SEND APPROVAL/CANCEL BACK TO INVENTORY
// ---------------------------------------------------------
if ($action === 'update_status') {
    // 1. Read the JSON sent from our frontend modal
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->po_id) || empty($data->new_status)) {
        echo json_encode(['success' => false, 'message' => 'Missing PO ID or Status.']);
        exit();
    }

    // 2. Package the data as standard Form POST data for the external API
    $postData = http_build_query([
        'po_id'  => $data->po_id,
        'status' => $data->new_status
    ]);

    // 3. Setup the cURL POST request to the external Inventory API
    $url = "https://icis-inventory.onrender.com/includes/api/api.php?action=update_po_status";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent SSL blocks
    
    // 4. Execute the request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    // 5. Check if the external API accepted our request
    if ($httpCode === 200 && $response) {
        // We assume the external API sends back a JSON response like {"success": true, "message": "..."}
        // So we just echo their exact response straight back to our frontend!
        echo $response;
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update the external Inventory system. HTTP Code: ' . $httpCode . ' Error: ' . $curlError
        ]);
    }
    exit();
}

// Default fallback
echo json_encode(['success' => false, 'message' => 'Invalid action requested.']);
?>
