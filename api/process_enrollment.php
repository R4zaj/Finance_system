<?php
// api/process_enrollment.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit();
}

if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
    echo json_encode(['success' => false, 'message' => 'First Name and Last Name are required.']);
    exit();
}

$first_name  = trim($_POST['first_name']);
$last_name   = trim($_POST['last_name']);
$email       = !empty($_POST['email']) ? trim($_POST['email']) : null; 

try {
    // We insert the student, but leave enrollment_date as NULL to mark them as "Pending"
    $stmt = $pdo->prepare("
        INSERT INTO students (first_name, last_name, email, enrollment_date) 
        VALUES (:fname, :lname, :email, NULL)
    ");
    
    $stmt->execute([
        'fname' => $first_name,
        'lname' => $last_name,
        'email' => $email
    ]);

    echo json_encode(['success' => true, 'message' => 'Enrollment submitted for approval.']);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
