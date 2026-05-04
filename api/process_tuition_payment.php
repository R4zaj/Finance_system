<?php
// api/process_tuition_payment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->student_id) || empty($data->amount) || empty($data->pay_date) || empty($data->description)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required.']);
    exit();
}

try {
    $pdo->beginTransaction();

    // 1. Record the Student Payment
    $stmtPay = $pdo->prepare("INSERT INTO student_payments (student_id, amount, pay_date, description) VALUES (:sid, :amt, :pdate, :desc)");
    $stmtPay->execute([
        'sid' => $data->student_id,
        'amt' => $data->amount,
        'pdate' => $data->pay_date,
        'desc' => $data->description
    ]);

    // Fetch Student Name for the Ledger Description
    $stmtStudent = $pdo->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM students WHERE student_id = ?");
    $stmtStudent->execute([$data->student_id]);
    $studentName = $stmtStudent->fetchColumn();
    $ledgerDesc = "Tuition Payment: " . $studentName . " - " . $data->description;

    // 2. General Ledger: DEBIT Cash (Account ID 1 increases)
    $stmtLedgerDebit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (1, :pdate, :amt, 'Debit', :desc)");
    $stmtLedgerDebit->execute(['pdate' => $data->pay_date, 'amt' => $data->amount, 'desc' => $ledgerDesc]);

    // 3. General Ledger: CREDIT Tuition Revenue (Account ID 6 increases)
    $stmtLedgerCredit = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (6, :pdate, :amt, 'Credit', :desc)");
    $stmtLedgerCredit->execute(['pdate' => $data->pay_date, 'amt' => $data->amount, 'desc' => $ledgerDesc]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Payment processed and ledger updated.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>