<?php
// api/login_api.php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once '../includes/db.php'; // Adjust the path based on your folder structure

// Retrieve the JSON payload from the AJAX request
$data = json_decode(file_get_contents("php://input"));

// Input validation
if (empty($data->username) || empty($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "status" => "error", 
        "message" => "Please enter both username and password."
    ]);
    exit();
}

$username = trim($data->username);
$password = trim($data->password);

try {
    // Query the users table from your finance_system database
    $stmt = $pdo->prepare("SELECT id, username, password, role, employee_id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // The database dump uses bcrypt hashes ($2y$10$...) so we use password_verify
    if ($user && password_verify($password, $user['password'])) {
        
        // Set session variables for the authenticated user
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['employee_id'] = $user['employee_id'];

        http_response_code(200); // OK
        echo json_encode([
            "status" => "success",
            "message" => "Authentication successful",
            "role" => $user['role'],
            "redirect" => "pages/dashboard.php" // Or wherever you route users after login
        ]);
    } else {
        http_response_code(401); // Unauthorized
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password."
        ]);
    }
} catch (Exception $e) {
    http_response_code(500); // Server Error
    echo json_encode([
        "status" => "error",
        "message" => "System error occurred. Please try again later."
    ]);
}
?>