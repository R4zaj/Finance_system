<?php
// api/get_tuition_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

try {
    // 1. Get recent payments
    $stmtPayments = $pdo->query("
        SELECT sp.payment_id, sp.amount, sp.pay_date, sp.description, s.first_name, s.last_name 
        FROM student_payments sp
        JOIN students s ON sp.student_id = s.student_id
        ORDER BY sp.pay_date DESC LIMIT 50
    ");
    $payments = $stmtPayments->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get list of students for the Payment Form Dropdown
    $stmtStudents = $pdo->query("SELECT student_id, first_name, last_name FROM students ORDER BY last_name ASC");
    $students = $stmtStudents->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'payments' => $payments,
        'students' => $students
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>