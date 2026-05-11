<?php
// api/manage_transaction.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$action = $data->action ?? '';
$transaction_id = $data->transaction_id ?? null;

if (!$transaction_id || !in_array($action, ['void', 'delete'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}

try {
    if ($action === 'delete') {
        // 🚨 STRICT ROLE GUARD: Only Admins can execute a hard delete
        if ($_SESSION['role'] !== 'admin') {
            echo json_encode(['success' => false, 'message' => 'Access Denied: Only Administrators can permanently delete records.']);
            exit();
        }

        $stmt = $pdo->prepare("DELETE FROM transactions WHERE transaction_id = :id");
        $stmt->execute(['id' => $transaction_id]);
        
        echo json_encode(['success' => true, 'message' => 'Transaction permanently deleted.']);
    } 
    
    elseif ($action === 'void') {
        // We prepend [VOIDED] to the description and set the amount to 0 so it cancels out.
        // We also ensure we don't accidentally void it twice.
        $stmt = $pdo->prepare("
            UPDATE transactions 
            SET amount = 0.00, 
                description = CONCAT('[VOIDED] ', description) 
            WHERE transaction_id = :id 
            AND description NOT LIKE '[VOIDED]%'
        ");
        $stmt->execute(['id' => $transaction_id]);
        
        echo json_encode(['success' => true, 'message' => 'Transaction successfully voided.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
