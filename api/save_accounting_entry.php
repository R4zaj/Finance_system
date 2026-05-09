<?php
// api/save_accounting_entry.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->entry_date) || empty($data->account_id) || empty($data->description)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

// Ensure they entered a value greater than 0 in either debit or credit
$debitAmount = floatval($data->debit);
$creditAmount = floatval($data->credit);

if ($debitAmount <= 0 && $creditAmount <= 0) {
    echo json_encode(['success' => false, 'message' => 'You must enter a Debit or Credit amount greater than zero.']);
    exit();
}

// Determine the type and the final amount based on your `transactions` table schema
$type = '';
$amount = 0.00;

if ($debitAmount > 0) {
    $type = 'Debit';
    $amount = $debitAmount;
} elseif ($creditAmount > 0) {
    $type = 'Credit';
    $amount = $creditAmount;
}

try {
    // Insert into your existing `transactions` table
    $stmt = $pdo->prepare("
        INSERT INTO transactions (account_id, trans_date, amount, type, description) 
        VALUES (:account, :date, :amount, :type, :desc)
    ");
    
    $stmt->execute([
        'account' => $data->account_id, // e.g., 1 for Cash, 2 for AR
        'date'    => $data->entry_date,
        'amount'  => $amount,
        'type'    => $type,
        'desc'    => $data->description
    ]);

    echo json_encode(['success' => true, 'message' => 'Accounting transaction recorded successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
