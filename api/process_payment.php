<?php
// api/process_payment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->student_id) || empty($data->pay_date) || empty($data->amount) || empty($data->description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill out all fields.']);
    exit();
}

try {
    // 1. Record the receipt in student_payments
    $payStmt = $pdo->prepare("
        INSERT INTO student_payments (student_id, amount, pay_date, description) 
        VALUES (:student_id, :amount, :pay_date, :description)
    ");
    $payStmt->execute([
        'student_id'  => $data->student_id,
        'amount'      => $data->amount,
        'pay_date'    => $data->pay_date,
        'description' => $data->description
    ]);

    // 2. Fetch the student's name for a clean Ledger description
    $nameStmt = $pdo->prepare("SELECT first_name, last_name FROM students WHERE student_id = :id");
    $nameStmt->execute(['id' => $data->student_id]);
    $student = $nameStmt->fetch(PDO::FETCH_ASSOC);
    $fullName = $student ? trim($student['first_name'] . ' ' . $student['last_name']) : 'Student';

    // 3. Log the income to the Master Ledger (Account ID 1 = Cash/Bank)
    $ledgerDesc = "Tuition Collection - " . $fullName . " (" . $data->description . ")";
    $ledgerStmt = $pdo->prepare("
        INSERT INTO transactions (trans_date, account_id, description, amount, type) 
        VALUES (:pay_date, 1, :desc, :amount, 'Credit')
    ");
    $ledgerStmt->execute([
        'pay_date' => $data->pay_date,
        'desc'     => $ledgerDesc,
        'amount'   => $data->amount
    ]);

    echo json_encode(['success' => true, 'message' => 'Payment recorded successfully!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
