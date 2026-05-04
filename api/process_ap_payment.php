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
    $ledgerDesc = "Vendor Payment: " . $supplierName . " (PO #" . $data->po_id . ")";

    // 2. GL Entry: DEBIT Accounts Payable (Account 4 decreases liability)
    $stmtDebit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (4, :pdate, :amt, 'Debit', :desc)");
    $stmtDebit->execute(['pdate' => $data->pay_date, 'amt' => $data->amount, 'desc' => $ledgerDesc]);

    // 3. GL Entry: CREDIT Cash (Account 1 decreases asset)
    $stmtCredit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (1, :pdate, :amt, 'Credit', :desc)");
    $stmtCredit->execute(['pdate' => $data->pay_date, 'amt' => $data->amount, 'desc' => $ledgerDesc]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Vendor paid and ledger updated successfully.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>