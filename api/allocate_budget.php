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
    // UPSERT: We use :amount1 and :amount2 to satisfy strict mode!
    $stmt = $pdo->prepare("
        INSERT INTO budgets (department_id, year, allocated_amount) 
        VALUES (:dept_id, :year, :amount1)
        ON DUPLICATE KEY UPDATE allocated_amount = :amount2
    ");
    
    // We pass the amount twice to match the two placeholders
    $stmt->execute([
        'dept_id' => $data->department_id,
        'year'    => $data->year,
        'amount1' => $data->amount,
        'amount2' => $data->amount
    ]);

    echo json_encode(['success' => true, 'message' => 'Budget allocated successfully.']);
} catch (PDOException $e) {
    // We expose the real error message now so it can't hide from us!
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
