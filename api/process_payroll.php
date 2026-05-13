<?php
// api/process_payroll.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

$data = json_decode(file_get_contents("php://input"));
$pay_period = $data->pay_period ?? date('Y-m'); // e.g., "2026-05"

try {
    $pdo->beginTransaction();

    // 1. FIX: Changed 'id' to 'employee_id' to match your database!
    $stmtEmp = $pdo->query("SELECT employee_id, salary FROM employees WHERE status = 'Active'");
    $employees = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

    if (empty($employees)) {
        throw new Exception("No active employees found to process.");
    }

    $total_gross = 0;
    $total_net = 0;

    // 2. FIX: Adjusted 'gross_pay' to 'gross_amount' and 'payment_date' to 'pay_date'
    $insertPayment = $pdo->prepare("
        INSERT INTO employee_payments (employee_id, pay_period, gross_amount, deductions, net_amount, pay_date) 
        VALUES (:emp_id, :period, :gross, :deductions, :net, CURRENT_DATE)
    ");

    foreach ($employees as $emp) {
        $gross = $emp['salary'];
        // Placeholder for real unpaid leave / tax calculation logic
        $deductions = $gross * 0.10; 
        $net = $gross - $deductions;

        $insertPayment->execute([
            'emp_id'     => $emp['employee_id'], // FIX: Pulling 'employee_id' instead of 'id'
            'period'     => $pay_period,
            'gross'      => $gross,
            'deductions' => $deductions,
            'net'        => $net
        ]);

        $total_gross += $gross;
        $total_net += $net;
    }

    // 3. Post to General Ledger (Finance Integration)
    $stmtAcc = $pdo->query("SELECT account_id, name FROM accounts WHERE name LIKE '%Salary%' OR name LIKE '%Cash%' LIMIT 2");
    $accounts = $stmtAcc->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Fallbacks if exact names aren't found in your DB
    $expense_account = array_search('Salary Expense', $accounts) ?: array_key_first($accounts);
    $cash_account = array_search('Cash in Bank', $accounts) ?: array_key_last($accounts);

    $glDesc = "Master Payroll Run for period: " . $pay_period;

    // Debit: Salary Expense (Using Gross Pay)
    $stmtGL = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, CURRENT_DATE, ?, 'Debit', ?)");
    $stmtGL->execute([$expense_account, $total_gross, $glDesc]);

    // Credit: Cash (Using Net Pay - assuming deductions are payable to tax agencies later)
    $stmtGL = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, CURRENT_DATE, ?, 'Credit', ?)");
    $stmtGL->execute([$cash_account, $total_net, $glDesc]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "Payroll for $pay_period processed successfully. Financial ledgers updated."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Payroll failed: ' . $e->getMessage()]);
}
?>
