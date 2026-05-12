<?php
// api/get_department_details.php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$dept_id = isset($_GET['dept_id']) ? (int)$_GET['dept_id'] : 0;
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

if ($dept_id === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Department ID']);
    exit();
}

try {
    $expenses = [];

    // 1. Fetch Payroll Expenses (Native Database Links)
    $payrollStmt = $pdo->prepare("
        SELECT ep.pay_date as date, 'Payroll' as type, CONCAT('Salary: ', e.first_name, ' ', e.last_name) as description, ep.gross_amount as amount
        FROM employee_payments ep
        JOIN employees e ON ep.employee_id = e.employee_id
        WHERE e.department_id = :id AND YEAR(ep.pay_date) = :year
    ");
    $payrollStmt->execute(['id' => $dept_id, 'year' => $year]);
    $payrollData = $payrollStmt->fetchAll(PDO::FETCH_ASSOC);
    $expenses = array_merge($expenses, $payrollData);

    // 2. Fetch Tagged Ledger Transactions (The Tag Hack)
    $tag = "[DEPT-" . $dept_id . "]";
    $transStmt = $pdo->prepare("
        SELECT trans_date as date, 'Ledger' as type, REPLACE(description, :tag, '') as description, amount
        FROM transactions 
        WHERE type = 'Debit' 
        AND description LIKE :searchTag 
        AND description NOT LIKE '[VOIDED]%'
        AND YEAR(trans_date) = :year
    ");
    $transStmt->execute(['tag' => $tag, 'searchTag' => "%$tag%", 'year' => $year]);
    $transData = $transStmt->fetchAll(PDO::FETCH_ASSOC);
    $expenses = array_merge($expenses, $transData);

    // 3. Sort all expenses by Date (Newest First)
    usort($expenses, function($a, $b) {
        return strtotime($b['date']) - strtotime($a['date']);
    });

    echo json_encode(['success' => true, 'data' => $expenses]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
