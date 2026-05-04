<?php
// api/logout_api.php
session_start();
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Destroy all session data
session_unset();
session_destroy();

http_response_code(200);
echo json_encode([
    "status" => "success",
    "message" => "Logged out successfully",
    "redirect" => "/"
]);
?>