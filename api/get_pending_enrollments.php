<?php
// api/get_pending_enrollments.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

try {
    // A student is "Pending" if they have no enrollment date yet
    $stmt = $pdo->prepare("
        SELECT student_id, first_name, last_name, COALESCE(email, 'No Email Provided') as email 
        FROM students 
        WHERE enrollment_date IS NULL 
        ORDER BY student_id ASC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $students]);
} catch (PDOException $e) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
