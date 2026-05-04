<?php
// api/get_accounts.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// Security Check: Only allow logged-in users
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

// Include your database connection
require_once '../includes/db.php';

try {
    // Fetch all accounts
    $stmt = $pdo->prepare("SELECT account_id, name, type FROM accounts ORDER BY account_id ASC");
    $stmt->execute();
    $accounts = $stmt->fetchAll();

    // Return success JSON
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "data" => $accounts
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => "Database error. Failed to retrieve accounts."
    ]);
}
?>