<?php
// api/process_ap_payment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->po_id) || empty($data->supplier_id) || empty($data->amount) || empty($data->pay_date)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Record the Vendor Payment
    $stmtPay = $pdo->prepare("
        INSERT INTO vendor_payments (supplier_id, po_id, pay_date, amount) 
        VALUES (:sid, :poid, :pdate, :amt)
    ");
    $stmtPay->execute([
        'sid' => $data->supplier_id,
        'poid' => $data->po_id,
        'pdate' => $data->pay_date,
        'amt' => $data->amount
    ]);

    // Fetch Supplier Name for Ledger Description
    $stmtSupp = $pdo->prepare("SELECT name FROM suppliers WHERE supplier_id = ?");
    $stmtSupp->execute([$data->supplier_id]);
    $supplierName = $stmtSupp->fetchColumn();
    
    // Fallback if supplier isn't found
    if (!$supplierName) $supplierName = "Unknown Vendor";
    
    $ledgerDesc = "Vendor Payment: " . $supplierName . " (PO #" . $data->po_id . ")";

    // ==========================================
    // 2 & 3. SMART MASTER LEDGER SYNC
    // ==========================================
    
    // We know Account 1 is your Main Cash/Bank account
    $cash_account = 1; 

    // Dynamically find your Accounts Payable OR a generic Expense account
    $stmtAcc = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Payable%' OR name LIKE '%Expense%' OR name LIKE '%Purchases%' LIMIT 1");
    $ap_expense_account = $stmtAcc->fetchColumn();
    
    // If it literally can't find one, fallback to 4 (your original setup)
    if (!$ap_expense_account) {
        $ap_expense_account = 4; 
    }

    // GL Entry: DEBIT Accounts Payable / Expense (Increases expense or decreases liability)
    $stmtDebit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, :pdate, :amt, 'Debit', :desc)");
    $stmtDebit->execute([
        $ap_expense_account,
        'pdate' => $data->pay_date, 
        'amt' => $data->amount, 
        'desc' => $ledgerDesc
    ]);

    // GL Entry: CREDIT Cash (Decreases asset)
    $stmtCredit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, :pdate, :amt, 'Credit', :desc)");
    $stmtCredit->execute([
        $cash_account,
        'pdate' => $data->pay_date, 
        'amt' => $data->amount, 
        'desc' => $ledgerDesc
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Vendor paid and ledger updated successfully.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
