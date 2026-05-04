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
$year = $_GET['year'] ?? date('Y');

try {
    // Calculate Allocated and Actual Spent using the exact provided schema constraints
    $query = "
        SELECT 
            d.department_id,
            d.name as department_name,
            COALESCE(b.allocated_amount, 0) as allocated,
            
            -- Actual Spent: Calculated via Departmental Payroll 
            -- (Joining employee_payments -> employees -> departments)
            (SELECT COALESCE(SUM(ep.gross_amount), 0) 
             FROM employee_payments ep
             JOIN employees e ON ep.employee_id = e.employee_id
             WHERE e.department_id = d.department_id AND YEAR(ep.pay_date) = :year) as actual_spent,
             
            -- Reserved: Set to 0 because purchase_orders is not tied to departments in the current DB
            0 as reserved
             
        FROM departments d
        LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = :year
        ORDER BY d.name ASC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['year' => $year]);
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'year' => $year, 'data' => $departments]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>