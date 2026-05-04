<?php
// api/enrollment_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

// ==============================================================================
// Note: If you have an external Enrollment API (like Render), you would use cURL 
// here just like in integration_inventory.php. 
// For now, we simulate the Enrollment Database/API response.
// ==============================================================================

if ($action === 'get_pending') {
    // Simulated data from an Enrollment Module
    $pending_enrollments = [
        [
            "enrollment_id" => "ENR-2026-001",
            "student_id" => "10045",
            "student_name" => "Juan Dela Cruz",
            "program" => "BS Information Technology",
            "year_level" => "2nd Year",
            "total_assessment" => "25000.00",
            "status" => "Pending Finance Approval"
        ],
        [
            "enrollment_id" => "ENR-2026-002",
            "student_id" => "10089",
            "student_name" => "Maria Clara",
            "program" => "BS Computer Science",
            "year_level" => "1st Year",
            "total_assessment" => "24500.00",
            "status" => "Pending Finance Approval"
        ]
    ];
    
    echo json_encode(['status' => 'success', 'data' => $pending_enrollments]);
    exit();
}

if ($action === 'approve') {
    $enrollment_id = $_POST['enrollment_id'] ?? '';
    
    if (empty($enrollment_id)) {
        echo json_encode(['status' => 'error', 'message' => 'Enrollment ID is required.']);
        exit();
    }

    // Here you would normally run an UPDATE query to your local enrollments table
    // OR send a POST request via cURL to the external Registrar API.
    
    // Example Local DB Update:
    // require_once '../includes/db.php';
    // $stmt = $pdo->prepare("UPDATE enrollments SET status = 'Officially Enrolled' WHERE enrollment_id = ?");
    // $stmt->execute([$enrollment_id]);

    echo json_encode([
        'status' => 'success', 
        'message' => "Enrollment $enrollment_id has been approved. Student is officially enrolled."
    ]);
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
?>