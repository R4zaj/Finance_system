<?php
// api/allocate_budget.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));

if (empty($data->department_id) || empty($data->year) || !isset($data->amount)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
    exit();
}

try {
    // UPSERT: Insert new budget or update existing one for that year/department
    $stmt = $pdo->prepare("
        INSERT INTO budgets (department_id, year, allocated_amount) 
        VALUES (:dept_id, :year, :amount)
        ON DUPLICATE KEY UPDATE allocated_amount = :amount
    ");
    
    $stmt->execute([
        'dept_id' => $data->department_id,
        'year'    => $data->year,
        'amount'  => $data->amount
    ]);

    echo json_encode(['success' => true, 'message' => 'Budget allocated successfully.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
?>