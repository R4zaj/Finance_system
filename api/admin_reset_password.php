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

// 2. Read Data securely (This trick safely handles both JSON payloads and standard Form Data)
$data = json_decode(file_get_contents("php://input"), true);

// Safely extract the variables
$target_user_id = isset($data['target_user_id']) ? $data['target_user_id'] : (isset($_POST['target_user_id']) ? $_POST['target_user_id'] : '');
$new_password = isset($data['new_password']) ? $data['new_password'] : (isset($_POST['new_password']) ? $_POST['new_password'] : '');

// 3. ULTRA-SPECIFIC VALIDATION: So we know exactly what is failing!
if (empty($target_user_id) && empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Error: Both User ID and Password are missing from the request.']);
    exit();
}
if (empty($target_user_id)) {
    echo json_encode(['success' => false, 'message' => 'Error: User ID is missing! The dropdown is failing to capture the database ID.']);
    exit();
}
if (empty($new_password)) {
    echo json_encode(['success' => false, 'message' => 'Error: The new password field was not received by the server.']);
    exit();
}

try {
    // 4. Hash the new password securely
    $newHash = password_hash($new_password, PASSWORD_DEFAULT);

    // 5. Update the specific user's password in the database
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute([
        'password' => $newHash,
        'id'       => $target_user_id
    ]);

    // Check if a row was actually modified
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'User password successfully overwritten!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Password reset failed. Ensure the user ID exists in the database.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
