<?php
// api/procurement_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

$action = isset($_GET['action']) ? $_GET['action'] : '';

// ---------------------------------------------------------
// ACTION 1: FETCH POs FROM INVENTORY
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
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->po_id) || empty($data->new_status)) {
        echo json_encode(['success' => false, 'message' => 'Missing PO ID or Status.']);
        exit();
    }

    // Package the exact keys the Inventory API is expecting
    $postData = http_build_query([
        'po_id'  => $data->po_id,
        'status' => $data->new_status
    ]);

    // Send the POST request directly back to the Inventory system
    $url = "https://icis-inventory.onrender.com/includes/api/api.php?action=update_po_status";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        echo $response;
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Failed to update Inventory API. HTTP: ' . $httpCode
        ]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action requested.']);
?>
