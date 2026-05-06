<?php
// api/approve_enrollment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->student_id)) {
    echo json_encode(['success' => false, 'message' => 'Student ID is missing.']);
    exit();
}

try {
    // Approving the student means stamping today's date as their enrollment date
    $stmt = $pdo->prepare("UPDATE students SET enrollment_date = CURDATE() WHERE student_id = :id");
    $stmt->execute(['id' => $data->student_id]);

    echo json_encode(['success' => true, 'message' => 'Student approved and officially enrolled.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
