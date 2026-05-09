<?php
// api/update_settings.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php'; // Ensure your DB connection is included

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in again.']);
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"));
$userId = $_SESSION['user_id'];

if (empty($data->current_password) || empty($data->new_password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields.']);
    exit();
}

try {
    // 1. Verify the current password
    // **NOTE**: Adjust the table name ('users') and column name ('password') if your DB uses different names!
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
    $stmt->execute(['id' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($data->current_password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Current password is incorrect.']);
        exit();
    }

    // 2. Hash the new password
    $newHash = password_hash($data->new_password, PASSWORD_DEFAULT);

    // 3. Update the database
    $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $updateStmt->execute([
        'password' => $newHash,
        'id' => $userId
    ]);

    echo json_encode(['success' => true, 'message' => 'Password updated successfully!']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
