<?php
// api/process_journal_entry.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

// 1. Read from standard $_POST
if (empty($_POST['trans_date']) || empty($_POST['amount']) || empty($_POST['account_id']) || empty($_POST['trans_type']) || empty($_POST['description'])) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
    exit();
}

// 2. Map the fields
$trans_date = $_POST['trans_date'];
$amount = $_POST['amount'];
$account_id = $_POST['account_id'];
$trans_type = $_POST['trans_type']; // 'Debit' or 'Credit'
$description = $_POST['description'];

try {
    // 3. Insert the entry (Removed department_id from the query!)
    $stmt = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (:acc, :tdate, :amt, :ttype, :desc)");
    
    $stmt->execute([
        'acc' => $account_id, 
        'tdate' => $trans_date, 
        'amt' => $amount, 
        'ttype' => $trans_type,
        'desc' => "Manual Entry: " . $description
    ]);

    echo json_encode(['success' => true, 'message' => 'Journal entry posted successfully.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Transaction failed: ' . $e->getMessage()]);
}
?>
