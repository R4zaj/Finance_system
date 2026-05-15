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
    
    // Updated label to reflect that this is now the OFFICIAL EXPENSE
    $ledgerDesc = "Vendor Payment & Expense: " . $supplierName . " (PO #" . $data->po_id . ")";

    // ==========================================
    // 2. MASTER LEDGER SYNC (The Official Expense)
    // ==========================================
    
    // We know Account 1 is your Main Cash/Bank account
    $cash_account = 1; 

    // Find the Supplies/Purchases Expense account (Safeguarded against Salary/Payroll!)
    $stmtExp = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Supplies%' OR name LIKE '%Purchases%' OR name LIKE '%Procurement%' LIMIT 1");
    $exp_account = $stmtExp->fetchColumn();
    
    if (!$exp_account) {
        $stmtExpFallback = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Expense%' AND name NOT LIKE '%Salary%' LIMIT 1");
        $exp_account = $stmtExpFallback->fetchColumn() ?: 5; 
    }

    // DEBIT: The Expense Account (Officially recognizing the cost now that we are paying)
    $stmtDebit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, :pdate, :amt, 'Debit', :desc)");
    $stmtDebit->execute([
        $exp_account,
        'pdate' => $data->pay_date, 
        'amt' => $data->amount, 
        'desc' => $ledgerDesc
    ]);

    // CREDIT: The Cash Account (Decreases asset because money left the bank)
    $stmtCredit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, :pdate, :amt, 'Credit', :desc)");
    $stmtCredit->execute([
        $cash_account,
        'pdate' => $data->pay_date, 
        'amt' => $data->amount, 
        'desc' => $ledgerDesc
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Vendor paid and Master Ledger officially updated.']);

} catch (Exception $e) {
    $pdo->rollBack();
    // Returning the exact error message so you can debug if a database issue occurs!
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
