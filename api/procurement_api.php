<?php
// api/procurement_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// NEW: Connect to the Local Finance Database
require_once '../includes/db.php';

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
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        echo $response;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to connect to the Inventory API. HTTP: ' . $httpCode]);
    }
    exit();
}

// ---------------------------------------------------------
// ACTION 2: SEND APPROVAL/CANCEL BACK TO INVENTORY & SYNC LEDGER
// ---------------------------------------------------------
if ($action === 'update_status') {
    $data = json_decode(file_get_contents("php://input"));
    
    if (empty($data->po_id) || empty($data->new_status)) {
        echo json_encode(['status' => 'error', 'message' => 'Missing PO ID or Status.']);
        exit();
    }

    // 1. Send update to the external Inventory API
    $postData = http_build_query([
        'po_id'  => $data->po_id,
        'status' => $data->new_status
    ]);

    $url = "https://icis-inventory.onrender.com/includes/api/api.php?action=update_po_status";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // 2. If Inventory accepted it, SYNC FINANCES LOCALLY!
    if ($httpCode === 200 && $response) {
        
        if ($data->new_status === 'Approved') {
            try {
                // Capture details sent from the frontend JS
                $poAmount = isset($data->amount) ? (float)$data->amount : 0.00;
                $suppId = isset($data->supplier_id) ? $data->supplier_id : 999;
                $suppName = isset($data->supplier_name) ? $data->supplier_name : 'External Vendor';
                
                // NEW: Capture the Department ID from JavaScript (default to 1 if empty)
                $deptId = isset($data->department_id) ? (int)$data->department_id : 1;
                
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

                // A. Save Supplier Locally (if they don't exist yet)
                $chkSupp = $pdo->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
                $chkSupp->execute([$suppId]);
                if (!$chkSupp->fetch()) {
                    $insSupp = $pdo->prepare("INSERT INTO suppliers (supplier_id, name) VALUES (?, ?)");
                    $insSupp->execute([$suppId, $suppName]);
                }

                // B. Save PO Locally WITH the Department ID so the Budget module can see it!
                $insPO = $pdo->prepare("
                    INSERT IGNORE INTO purchase_orders (po_id, supplier_id, department_id, order_date, total_amount, status) 
                    VALUES (?, ?, ?, CURDATE(), ?, 'Approved')
                ");
                $insPO->execute([$data->po_id, $suppId, $deptId, $poAmount]);

                // C. BUDGET ENCUMBRANCE ONLY (No Ledger Update!)
                // We NO LONGER insert into the `transactions` table here.
                // Just by saving the PO in the line above, the budget is officially "Reserved".
                // The actual Expense will be logged when you click "Process Payment" in Accounts Payable.
                
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            } catch (Exception $e) {
                $pdo->exec("SET FOREIGN_KEY_CHECKS=1;");
            }
        }
        
        echo $response;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update Inventory API. HTTP: ' . $httpCode]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action requested.']);
?>
