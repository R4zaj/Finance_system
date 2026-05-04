<?php
// api/get_ledger.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

require_once '../includes/db.php'; // Updated path based on your structure

try {
    // Join transactions with accounts to get the account name and type
    $query = "SELECT t.trans_date, a.name AS account_name, a.type AS account_type, 
                     t.description, t.amount, t.type AS trans_type
              FROM transactions t
              JOIN accounts a ON t.account_id = a.account_id
              ORDER BY t.trans_date DESC";
              
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $ledger = $stmt->fetchAll();

    echo json_encode([
        "status" => "success",
        "data" => $ledger
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>