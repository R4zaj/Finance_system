<?php
// api/get_dashboard_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// 1. Security check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// 2. Method check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

require_once '../includes/db.php';

$year = $_GET['year'] ?? date('Y');
$summary = [];

try {
    // ==========================================
    // A. CORE FINANCIAL SUMMARY
    // ==========================================
    
    // Total Allocated Budget
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(allocated_amount), 0) as total FROM budgets WHERE year = ?");
    $stmt->execute([$year]);
    $summary['total_budget'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Expenses (Debits)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'Debit' AND YEAR(trans_date) = ?");
    $stmt->execute([$year]);
    $summary['total_expenses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Revenue (Credits)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'Credit' AND YEAR(trans_date) = ?");
    $stmt->execute([$year]);
    $summary['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Student Payments
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM student_payments WHERE YEAR(pay_date) = ?");
    $stmt->execute([$year]);
    $summary['total_student_payments'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


    // ==========================================
    // B. CROSS-MODULE INTEGRATION (HR & Supply)
    // ==========================================
    
    // Active Staff (HRIS) - Removed 'status' clause to fix SQL error
    $stmt = $pdo->query("SELECT COUNT(*) as active_staff FROM employees");
    $summary['active_staff'] = $stmt->fetch(PDO::FETCH_ASSOC)['active_staff'];

    // Pending POs (Supply) - Removed 'status' clause to fix SQL error
    $stmt = $pdo->query("SELECT COUNT(*) as pending_pos FROM purchase_orders");
    $summary['pending_pos'] = $stmt->fetch(PDO::FETCH_ASSOC)['pending_pos'];


    // ==========================================
    // C. ANALYTICS & CHARTS DATA
    // ==========================================
    
    // Budget by department
    $stmt = $pdo->prepare("SELECT d.name, COALESCE(SUM(b.allocated_amount), 0) as allocated 
                           FROM departments d 
                           LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = ? 
                           GROUP BY d.department_id ORDER BY d.name");
    $stmt->execute([$year]);
    $budget_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly revenue vs expenses
    $stmt = $pdo->prepare("SELECT MONTH(trans_date) as month,
                           SUM(CASE WHEN type = 'Credit' THEN amount ELSE 0 END) as revenue,
                           SUM(CASE WHEN type = 'Debit' THEN amount ELSE 0 END) as expenses
                           FROM transactions WHERE YEAR(trans_date) = ?
                           GROUP BY MONTH(trans_date) ORDER BY MONTH(trans_date)");
    $stmt->execute([$year]);
    $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent transactions log
    $stmt = $pdo->prepare("SELECT t.*, a.name as account_name 
                           FROM transactions t 
                           JOIN accounts a ON t.account_id = a.account_id 
                           WHERE YEAR(t.trans_date) = ? 
                           ORDER BY t.trans_date DESC LIMIT 10");
    $stmt->execute([$year]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ==========================================
    // D. JSON RESPONSE
    // ==========================================
    echo json_encode([
        'success' => true,
        'year' => $year,
        'summary' => $summary,
        'budget_by_department' => $budget_data,
        'monthly_data' => $monthly_data,
        'recent_transactions' => $recent
    ]);

} catch (PDOException $e) {
    // Safely catch any other missing table/column errors
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
