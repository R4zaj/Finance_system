<?php
// pages/transactions.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

require_once '../includes/db.php';

// Fetch all transactions (NO schema changes required)
try {
    $stmt = $pdo->query("
        SELECT t.transaction_id, t.trans_date, t.amount, t.type, t.description, a.name as account_name 
        FROM transactions t
        JOIN accounts a ON t.account_id = a.account_id
        ORDER BY t.trans_date DESC, t.transaction_id DESC
    ");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $transactions = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Transaction Ledger | Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; border-radius: 1rem; }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; border-bottom: 2px solid #dee2e6; }
        .voided-row { opacity: 0.6; background-color: #f8f9fa; }
        .voided-text { text-decoration: line-through; }
    </style>
</head>
<body class="d-flex vh-100 bg-light">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <?php include '../includes/topheader.php'; ?>

        <main class="p-4 overflow-y-auto">
            
            <div class="card action-banner p-4 mb-4 border-0 shadow-sm">
                <div>
                    <h3 class="fw-bold mb-1"><i class="bi bi-journal-richtext me-2"></i>Master Ledger</h3>
                    <p class="mb-0 text-white-50">View all financial transactions. Voiding creates an audit trail; deletion is restricted.</p>
                </div>
            </div>

            <div class="card card-custom shadow-sm p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle mb-0 bg-white">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Date</th>
                                <th>Account</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($transactions)): ?>
                                <tr><td colspan="7" class="text-center py-4 text-muted">No transactions found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($transactions as $t): ?>
                                    <?php 
                                        // Check if the description contains our hidden void flag
                                        $isVoided = (strpos($t['description'], '[VOIDED]') === 0); 
                                        
                                        // Clean up the description so the UI looks nice (removes the tag for display)
                                        $displayDescription = $isVoided ? trim(str_replace('[VOIDED]', '', $t['description'])) : $t['description'];

                                        $rowClass = $isVoided ? 'voided-row' : '';
                                        $textClass = $isVoided ? 'voided-text text-muted' : '';
                                    ?>
                                    <tr class="<?= $rowClass ?>">
                                        <td class="ps-4 text-muted small">#<?= $t['transaction_id'] ?></td>
                                        <td class="<?= $textClass ?>"><?= date('M d, Y', strtotime($t['trans_date'])) ?></td>
                                        <td class="fw-semibold <?= $textClass ?>"><?= htmlspecialchars($t['account_name']) ?></td>
                                        <td class="<?= $textClass ?>"><?= htmlspecialchars($displayDescription) ?></td>
                                        <td class="fw-bold <?= $t['type'] === 'Debit' ? 'text-danger' : 'text-success' ?> <?= $textClass ?>">
                                            <?= $t['type'] === 'Debit' ? '-' : '+' ?>₱<?= number_format($t['amount'], 2) ?>
                                        </td>
                                        <td>
                                            <?php if ($isVoided): ?>
                                                <span class="badge bg-secondary">Voided</span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-25 text-success">Active</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            
                                            <?php if (!$isVoided): ?>
                                                <button class="btn btn-sm btn-warning btn-action me-1" data-id="<?= $t['transaction_id'] ?>" data-action="void" title="Void Transaction">
                                                    <i class="bi bi-slash-circle"></i>
                                                </button>
                                            <?php endif; ?>

                                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                                <button class="btn btn-sm btn-danger btn-action" data-id="<?= $t['transaction_id'] ?>" data-action="delete" title="Permanently Delete">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            <?php endif; ?>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('.btn-action').on('click', function() {
            let action = $(this).data('action');
            let transId = $(this).data('id');
            
            // Confirm before taking action
            let msg = action === 'delete' 
                ? "WARNING: Are you sure you want to PERMANENTLY delete this transaction? This cannot be undone." 
                : "Are you sure you want to void this transaction? This will set the amount to ₱0.00.";
                
            if (!confirm(msg)) return;

            let $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: '../api/manage_transaction.php',
                type: 'POST',
                data: JSON.stringify({ action: action, transaction_id: transId }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        location.reload(); 
                    } else {
                        alert('Error: ' + response.message);
                        $btn.prop('disabled', false).html(action === 'delete' ? '<i class="bi bi-trash3"></i>' : '<i class="bi bi-slash-circle"></i>');
                    }
                },
                error: function() {
                    alert('Server communication error.');
                    $btn.prop('disabled', false).html(action === 'delete' ? '<i class="bi bi-trash3"></i>' : '<i class="bi bi-slash-circle"></i>');
                }
            });
        });
    });
    </script>
</body>
</html>
