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
    // 1. Manually check if a budget already exists for this exact department and year
    $checkStmt = $pdo->prepare("SELECT budget_id FROM budgets WHERE department_id = :dept_id AND year = :year");
    $checkStmt->execute([
        'dept_id' => $data->department_id, 
        'year' => $data->year
    ]);
    $existingBudget = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($existingBudget) {
        // 2. UPSERT: It exists! UPDATE the existing amount.
        $updateStmt = $pdo->prepare("UPDATE budgets SET allocated_amount = :amount WHERE budget_id = :budget_id");
        $updateStmt->execute([
            'amount' => $data->amount,
            'budget_id' => $existingBudget['budget_id']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Budget allocation successfully updated.']);
    } else {
        // 3. UPSERT: It does NOT exist! INSERT a brand new record.
        $insertStmt = $pdo->prepare("INSERT INTO budgets (department_id, year, allocated_amount) VALUES (:dept_id, :year, :amount)");
        $insertStmt->execute([
            'dept_id' => $data->department_id,
            'year' => $data->year,
            'amount' => $data->amount
        ]);
        
        echo json_encode(['success' => true, 'message' => 'New budget allocated successfully.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
