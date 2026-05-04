<?php
// api/get_asset_balance.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    // Notice the '=>' instead of ':' here!
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

try {
    // Assets have a "Debit" normal balance. 
    // So the total is: SUM(Debits) - SUM(Credits)
    $stmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN t.type = 'Debit' THEN t.amount ELSE 0 END) - 
            SUM(CASE WHEN t.type = 'Credit' THEN t.amount ELSE 0 END) AS total_assets
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        WHERE a.type = 'Asset'
    ");
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If there are no transactions yet, default to 0.00
    $total_assets = $result['total_assets'] ?? 0.00;

    // Send back the data (again, using '=>')
    echo json_encode([
        'success' => true,
        'total_assets' => (float) $total_assets
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>