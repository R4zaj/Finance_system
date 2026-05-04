<?php
// api/get_financial_reports.php
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
    $report = [
        'income_statement' => ['revenue' => [], 'expense' => [], 'total_revenue' => 0, 'total_expense' => 0, 'net_income' => 0],
        'balance_sheet' => ['asset' => [], 'liability' => [], 'total_asset' => 0, 'total_liability' => 0]
    ];

    // ==========================================
    // 1. INCOME STATEMENT (Strictly for the selected year)
    // ==========================================
    $stmtIS = $pdo->prepare("
        SELECT a.name, a.type, 
               SUM(CASE WHEN t.type = 'Credit' AND a.type = 'Revenue' THEN t.amount
                        WHEN t.type = 'Debit' AND a.type = 'Revenue' THEN -t.amount
                        WHEN t.type = 'Debit' AND a.type = 'Expense' THEN t.amount
                        WHEN t.type = 'Credit' AND a.type = 'Expense' THEN -t.amount
                        ELSE 0 END) as balance
        FROM accounts a
        JOIN transactions t ON a.account_id = t.account_id
        WHERE a.type IN ('Revenue', 'Expense') AND YEAR(t.trans_date) = :year
        GROUP BY a.account_id
        HAVING balance != 0
    ");
    $stmtIS->execute(['year' => $year]);
    
    foreach ($stmtIS->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = strtolower($row['type']);
        $report['income_statement'][$type][] = $row;
        $report['income_statement']["total_$type"] += $row['balance'];
    }
    $report['income_statement']['net_income'] = $report['income_statement']['total_revenue'] - $report['income_statement']['total_expense'];

    // ==========================================
    // 2. BALANCE SHEET (Cumulative up to selected year)
    // ==========================================
    $stmtBS = $pdo->prepare("
        SELECT a.name, a.type, 
               SUM(CASE WHEN t.type = 'Debit' AND a.type = 'Asset' THEN t.amount
                        WHEN t.type = 'Credit' AND a.type = 'Asset' THEN -t.amount
                        WHEN t.type = 'Credit' AND a.type = 'Liability' THEN t.amount
                        WHEN t.type = 'Debit' AND a.type = 'Liability' THEN -t.amount
                        ELSE 0 END) as balance
        FROM accounts a
        JOIN transactions t ON a.account_id = t.account_id
        WHERE a.type IN ('Asset', 'Liability') AND YEAR(t.trans_date) <= :year
        GROUP BY a.account_id
        HAVING balance != 0
    ");
    $stmtBS->execute(['year' => $year]);

    foreach ($stmtBS->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $type = strtolower($row['type']);
        $report['balance_sheet'][$type][] = $row;
        $report['balance_sheet']["total_$type"] += $row['balance'];
    }

    echo json_encode(['success' => true, 'year' => $year, 'data' => $report]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>