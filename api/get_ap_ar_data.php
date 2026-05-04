<?php
// api/get_ap_ar_data.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';

try {
    // 1. Calculate Outstanding Accounts Payable (Unpaid/Partially Paid POs)
    $stmtAP = $pdo->query("
        SELECT 
            po.po_id, 
            s.supplier_id,
            s.name as supplier_name, 
            po.order_date, 
            po.total_amount,
            COALESCE((SELECT SUM(amount) FROM vendor_payments vp WHERE vp.po_id = po.po_id), 0) as paid_amount
        FROM purchase_orders po
        JOIN suppliers s ON po.supplier_id = s.supplier_id
        WHERE po.status != 'Cancelled'
        HAVING (po.total_amount - paid_amount) > 0
        ORDER BY po.order_date ASC
    ");
    $outstanding_ap = $stmtAP->fetchAll(PDO::FETCH_ASSOC);

    // 2. Recent Vendor Payments (AP History)
    $stmtAPHistory = $pdo->query("
        SELECT vp.pay_date, s.name as supplier_name, vp.amount, vp.po_id
        FROM vendor_payments vp
        JOIN suppliers s ON vp.supplier_id = s.supplier_id
        ORDER BY vp.pay_date DESC LIMIT 10
    ");
    $ap_history = $stmtAPHistory->fetchAll(PDO::FETCH_ASSOC);

    // 3. Quick AR Summary (Total Student Payments collected this year)
    $stmtAR = $pdo->query("
        SELECT COALESCE(SUM(amount), 0) as total_ar 
        FROM student_payments 
        WHERE YEAR(pay_date) = YEAR(CURDATE())
    ");
    $total_ar = $stmtAR->fetchColumn();

    echo json_encode([
        'success' => true,
        'outstanding_ap' => $outstanding_ap,
        'ap_history' => $ap_history,
        'total_ar' => $total_ar
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>