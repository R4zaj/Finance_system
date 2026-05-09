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

// Basic Accounting Check: Must have either a debit or credit
if ($data->debit == 0 && $data->credit == 0) {
    echo json_encode(['success' => false, 'message' => 'You must enter a Debit or Credit amount.']);
    exit();
}

try {
    // 🚨 IMPORTANT: Change 'general_ledger' and these column names to match your actual database table!
    $stmt = $pdo->prepare("
        INSERT INTO general_ledger (entry_date, account_id, description, debit, credit, created_by) 
        VALUES (:date, :account, :desc, :debit, :credit, :user)
    ");
    
    $stmt->execute([
        'date'    => $data->entry_date,
        'account' => $data->account_id,
        'desc'    => $data->description,
        'debit'   => $data->debit,
        'credit'  => $data->credit,
        'user'    => $_SESSION['user_id']
    ]);

    echo json_encode(['success' => true, 'message' => 'Journal entry recorded successfully.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
