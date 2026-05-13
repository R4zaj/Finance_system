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

    // 1. Fetch all employees
    $stmtEmp = $pdo->query("SELECT employee_id, salary FROM employees");
    $employees = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

    if (empty($employees)) {
        throw new Exception("No active employees found to process.");
    }

    $total_gross = 0;
    $total_net = 0;

    $insertPayment = $pdo->prepare("
        INSERT INTO employee_payments (employee_id, gross_amount, deductions, net_amount, pay_date) 
        VALUES (:emp_id, :gross, :deductions, :net, CURRENT_DATE)
    ");

    foreach ($employees as $emp) {
        $gross = $emp['salary'];
        // Using 10% deduction placeholder
        $deductions = $gross * 0.10; 
        $net = $gross - $deductions;

        $insertPayment->execute([
            'emp_id'     => $emp['employee_id'],
            'gross'      => $gross,
            'deductions' => $deductions,
            'net'        => $net
        ]);

        $total_gross += $gross;
        $total_net += $net;
    }

    // ==========================================
    // 3. POST TO GENERAL LEDGER (FIXED ACCOUNTS)
    // ==========================================
    
    // Find the Salary Expense account dynamically
    $stmtAcc = $pdo->query("SELECT account_id FROM accounts WHERE name LIKE '%Salary%' LIMIT 1");
    $expense_account = $stmtAcc->fetchColumn();
    if (!$expense_account) {
        $expense_account = 2; // Safe fallback just in case
    }
    
    // We know from your Tuition settings that Account 1 is your Main Cash/Bank account!
    $cash_account = 1; 

    // Description for the Ledger
    $glDesc = "Master Payroll Run for period: " . $pay_period;

    // Debit: Salary Expense (Using Gross Pay - Total company expense)
    $stmtGL = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, CURRENT_DATE, ?, 'Debit', ?)");
    $stmtGL->execute([$expense_account, $total_gross, $glDesc]);

    // Credit: Cash (Using Net Pay - Actual money leaving the bank)
    $stmtGL = $pdo->prepare("INSERT INTO transactions (account_id, trans_date, amount, type, description) VALUES (?, CURRENT_DATE, ?, 'Credit', ?)");
    $stmtGL->execute([$cash_account, $total_net, $glDesc]);

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "Payroll for $pay_period processed successfully. Financial ledgers updated."]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Payroll failed: ' . $e->getMessage()]);
}
?>
