<?php
// api/procurement_api.php
session_start();
header('Content-Type: application/json; charset=UTF-8');

// Security Check
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

require_once '../includes/db.php';
$action = $_REQUEST['action'] ?? '';

// Fetch Pending/Draft Purchase Orders
if ($action === 'get_pending') {
    try {
        $stmt = $pdo->query("
            SELECT p.po_id, s.name as supplier_name, p.order_date, p.total_amount, p.status 
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.supplier_id
            WHERE p.status IN ('Draft', 'Ordered')
            ORDER BY p.order_date DESC, p.po_id DESC
        ");
        $pending_pos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $pending_pos]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

// Update PO Status (Approve or Cancel)
if ($action === 'update_status') {
    $po_id = $_POST['po_id'] ?? '';
    $new_status = $_POST['status'] ?? ''; // Expecting 'Received' (Approved) or 'Cancelled'

    if (empty($po_id) || empty($new_status)) {
        echo json_encode(['status' => 'error', 'message' => 'PO ID and Status are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE purchase_orders SET status = :status WHERE po_id = :po_id");
        $stmt->execute(['status' => $new_status, 'po_id' => $po_id]);

        $action_text = ($new_status === 'Cancelled') ? 'cancelled' : 'approved and marked as received';
        
        echo json_encode([
            'status' => 'success', 
            'message' => "Purchase Order #$po_id has been $action_text."
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()]);
    }
    exit();
}
// Fetch Processed Purchase Orders (History)
if ($action === 'get_history') {
    try {
        $stmt = $pdo->query("
            SELECT p.po_id, s.name as supplier_name, p.order_date, p.total_amount, p.status 
            FROM purchase_orders p
            JOIN suppliers s ON p.supplier_id = s.supplier_id
            WHERE p.status IN ('Received', 'Cancelled')
            ORDER BY p.order_date DESC, p.po_id DESC
            LIMIT 100
        ");
        $history_pos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(['status' => 'success', 'data' => $history_pos]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
?>
