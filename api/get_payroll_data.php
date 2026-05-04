<?php
// api/get_payroll_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

try {
    // 1. Fetch Employees and join with positions table to get the title
    $stmtEmp = $pdo->query("
        SELECT e.employee_id, e.first_name, e.last_name, p.title as position, e.salary 
        FROM employees e
        LEFT JOIN positions p ON e.position_id = p.position_id
    ");
    $employees = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

    // Add estimated standard deductions (e.g., 10% tax/benefits) for preview purposes
    foreach ($employees as &$emp) {
        $emp['est_deductions'] = $emp['salary'] * 0.10; 
        $emp['est_net_pay'] = $emp['salary'] - $emp['est_deductions'];
    }

    // 2. Fetch Recent Payroll History (employee_payments table)
    $stmtHist = $pdo->query("
        SELECT ep.pay_id, ep.pay_date, ep.gross_amount, ep.deductions, ep.net_amount, e.first_name, e.last_name
        FROM employee_payments ep
        JOIN employees e ON ep.employee_id = e.employee_id
        ORDER BY ep.pay_date DESC, ep.pay_id DESC
        LIMIT 50
    ");
    $history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'employees' => $employees,
        'history' => $history
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>