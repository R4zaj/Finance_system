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
                
                $pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

                // A. Save Supplier Locally (if they don't exist yet)
                $chkSupp = $pdo->prepare("SELECT supplier_id FROM suppliers WHERE supplier_id = ?");
                $chkSupp->execute([$suppId]);
                if (!$chkSupp->fetch()) {
                    $insSupp = $pdo->prepare("INSERT INTO suppliers (supplier_id, name) VALUES (?, ?)");
                    $insSupp->execute([$suppId, $suppName]);
                }

                // B. Save PO Locally so your AP module can see it
                $insPO = $pdo->prepare("
                    INSERT IGNORE INTO purchase_orders (po_id, supplier_id, order_date, total_amount, status) 
                    VALUES (?, ?, CURDATE(), ?, 'Approved')
                ");
                $insPO->execute([$data->po_id, $suppId, $poAmount]);

                /// C. MASTER LEDGER UPDATE (Single-Entry: Expense Only)
                if ($poAmount > 0) {
                    
                    // 1. FIRST PRIORITY: Look strictly for Supplies, Purchases, or Procurement
                    $stmtExp = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Supplies%' OR name LIKE '%Purchases%' OR name LIKE '%Procurement%' LIMIT 1");
                    $exp_acc = $stmtExp->fetchColumn();
                    
                    // 2. SECOND PRIORITY: If no specific supplies account exists, find a generic Expense account, but EXCLUDE Salary!
                    if (!$exp_acc) {
                        $stmtExpFallback = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Expense%' AND name NOT LIKE '%Salary%' LIMIT 1");
                        $exp_acc = $stmtExpFallback->fetchColumn() ?: 5; // Final fallback to ID 5
                    }
                    
                    // Simple, clean label for your ledger
                    $expenseDesc = "Procurement Expense: " . $suppName . " (PO #" . $data->po_id . ")";
                    
                    // Log the Expense (Debit)
                    $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, CURDATE(), ?, 'Debit', ?)")->execute([$exp_acc, $poAmount, $expenseDesc]);
                    
                }
                
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
