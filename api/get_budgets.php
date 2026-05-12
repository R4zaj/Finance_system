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
    // 1. UPDATED QUERY: Added SUM() and GROUP BY to magically squash the database duplicates!
    $query = "
        SELECT 
            d.department_id, 
            d.name as department_name, 
            COALESCE(SUM(b.allocated_amount), 0) as allocated, 
            (SELECT COALESCE(SUM(ep.gross_amount), 0) 
             FROM employee_payments ep 
             JOIN employees e ON ep.employee_id = e.employee_id 
             WHERE e.department_id = d.department_id AND YEAR(ep.pay_date) = :year1) as payroll_spent 
        FROM departments d 
        LEFT JOIN budgets b ON d.department_id = b.department_id AND b.year = :year2 
        GROUP BY d.department_id, d.name
        ORDER BY d.name ASC
    ";

    $stmt = $pdo->prepare($query);
    
    // We hand the year to BOTH placeholders to satisfy strict mode
    $stmt->execute([
        'year1' => $year,
        'year2' => $year
    ]);
    
    $departments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Prepare the frontend data arrays
    $summary = ['allocated' => 0, 'reserved' => 0, 'spent' => 0, 'available' => 0];
    $budgetData = [];

    // Prepare the statement for tagged transactions once, outside the loop
    $transStmt = $pdo->prepare("
        SELECT COALESCE(SUM(amount), 0) 
        FROM transactions 
        WHERE type = 'Debit' 
        AND description LIKE :tag 
        AND description NOT LIKE '[VOIDED]%'
        AND YEAR(trans_date) = :year3
    ");

    foreach ($departments as $dept) {
        $deptId = $dept['department_id'];
        $allocated = (float) $dept['allocated'];
        $payrollSpent = (float) $dept['payroll_spent'];

        // Reserved (POs) - Set to 0 without DB changes
        $reserved = 0;

        // Calculate Actual Spent -> Standard Transactions using the Text Tagging Hack
        $tag = "[DEPT-" . $deptId . "]";
        $transStmt->execute([
            'tag' => "%$tag%",
            'year3' => $year
        ]);
        $transSpent = (float) $transStmt->fetchColumn();

        // Total Spent = Tagged Transactions + Native Payroll
        $totalSpent = $transSpent + $payrollSpent;
        
        // Calculate Available Balance
        $available = $allocated - $reserved - $totalSpent;
        
        // Calculate Utilization Percentage
        $utilization = $allocated > 0 ? (($totalSpent + $reserved) / $allocated) * 100 : 0;

        // Add to Global Summary Totals
        $summary['allocated'] += $allocated;
        $summary['reserved'] += $reserved;
        $summary['spent'] += $totalSpent;
        $summary['available'] += $available;

        // Build the array exactly how budgeting.js expects it
        $budgetData[] = [
            'id' => $deptId,
            'name' => $dept['department_name'],
            'allocated' => $allocated,
            'reserved' => $reserved,
            'spent' => $totalSpent,
            'available' => $available,
            'utilization' => min(100, $utilization) // Cap visually at 100%
        ];
    }

    // Output the formatted JSON
    echo json_encode([
        'success' => true,
        'year' => $year,
        'summary' => $summary,         // Feeds the 4 top cards
        'departments' => $budgetData   // Feeds the table rows
    ]);

} catch (PDOException $e) {
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
