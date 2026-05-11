<?php
// api/admin_reset_password.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

// 1. STRICT ROLE GUARD: Kick out anyone who isn't an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access Denied. Admin privileges required.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (empty($data->target_user_id) || empty($data->new_password)) {
    echo json_encode(['success' => false, 'message' => 'Please select a user and enter a new password.']);
    exit();
}

try {
    // 2. Hash the new password securely
    $newHash = password_hash($data->new_password, PASSWORD_DEFAULT);

    // 3. Update the specific user's password in the database
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([
        'password' => $newHash,
        'id'       => $data->target_user_id
    ]);

    echo json_encode(['success' => true, 'message' => 'User password successfully reset.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
