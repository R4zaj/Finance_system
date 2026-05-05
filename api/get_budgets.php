<?php
// api/get_budgets.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

try {
    // Notice :year1 and :year2 below!
    $query = "SELECT d.department_id, d.name as department_name, COALESCE(b.allocated_amount, 0) as allocated, (SELECT COALESCE(SUM(ep.gross_amount), 0) FROM employee_payments ep JOIN employees e ON ep.employee_id = e.employee_id WHERE e.department_id = d.department_id AND YEAR(ep.pay_date) = :year1) as actual_spent, 0 as reserved FROM departments d LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = :year2 ORDER BY d.name ASC";

    $stmt = $pdo->prepare($query);
    
    // We hand the year to BOTH placeholders to satisfy strict mode
    $stmt->execute([
        'year1' => $year,
        'year2' => $year
    ]);
    
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'year' => $year, 'data' => $departments]);

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
