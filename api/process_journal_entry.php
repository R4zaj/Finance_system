<?php
// api/process_journal_entry.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->trans_date) || empty($data->amount) || empty($data->debit_account) || empty($data->credit_account) || empty($data->description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

if ($data->debit_account == $data->credit_account) {
    echo json_encode(['success' => false, 'message' => 'Debit and Credit accounts cannot be the same.']);
    exit();
}

$dept_id = !empty($data->department_id) ? $data->department_id : null;

try {
    $pdo->beginTransaction();

    // 1. Insert DEBIT Entry
    $stmtDebit = $pdo->prepare("INSERT INTO transactions (account_id, department_id, trans_date, amount, type, description) VALUES (:acc, :dept, :tdate, :amt, 'Debit', :desc)");
    $stmtDebit->execute([
        'acc' => $data->debit_account, 'dept' => $dept_id, 'tdate' => $data->trans_date, 'amt' => $data->amount, 'desc' => "Manual Entry: " . $data->description
    ]);

    // 2. Insert CREDIT Entry
    $stmtCredit = $pdo->prepare("INSERT INTO transactions (account_id, department_id, trans_date, amount, type, description) VALUES (:acc, :dept, :tdate, :amt, 'Credit', :desc)");
    $stmtCredit->execute([
        'acc' => $data->credit_account, 'dept' => $dept_id, 'tdate' => $data->trans_date, 'amt' => $data->amount, 'desc' => "Manual Entry: " . $data->description
    ]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Journal entry posted successfully.']);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
