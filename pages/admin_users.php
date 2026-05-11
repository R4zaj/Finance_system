<?php
// pages/admin_users.php
session_start();

// 1. STRICT ROLE GUARD: Redirect non-admins instantly
if (!isset($_SESSION['user_id'])) { 
    header("Location: ../login.php"); 
    exit(); 
}
if ($_SESSION['role'] !== 'admin') {
    // Kick unauthorized users back to the main dashboard
    header("Location: dashboard.php"); 
    exit();
}

require_once '../includes/db.php';

// Fetch all non-admin users so the Admin can select them from a dropdown
try {
    $stmt = $pdo->query("SELECT id, username, role FROM users WHERE role != 'admin' ORDER BY username ASC");
    $subordinate_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $subordinate_users = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Management | Admin Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #2b2d42 0%, #1a1b26 100%); color: white; border-radius: 1rem; }
    </style>
</head>
<body class="d-flex vh-100 bg-light">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column h-100 overflow-hidden">
        <?php include '../includes/topheader.php'; ?>

        <main class="p-4 overflow-y-auto">
            
            <div class="card action-banner p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2 text-warning"></i>Admin Control Panel</h3>
                        <p class="mb-0 text-white-50">Manage system users, roles, and override credentials securely.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-lg-5">
                    <div class="card card-custom p-4 shadow-sm">
                        <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="bi bi-key me-2"></i>Force Password Reset</h5>
                        
                        <div id="resetAlert" class="alert d-none small py-2"></div>

                        <form id="adminResetForm">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Select User</label>
                                <select class="form-select bg-light" id="target_user_id" required>
                                    <option value="" selected disabled>Choose a finance officer...</option>
                                    <?php foreach ($subordinate_users as $user): ?>
                                        <option value="<?= htmlspecialchars($user['id']) ?>">
                                            <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars(strtoupper($user['role'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
<div class="mb-3">
    <label class="form-label small fw-bold text-muted">New Password</label>
    <input type="password" class="form-control bg-light" id="admin_new_password" required minlength="6">
</div>

<div class="mb-4">
    <label class="form-label small fw-bold text-muted">Confirm New Password</label>
    <input type="password" class="form-control bg-light" id="admin_confirm_password" required minlength="6">
</div>
                            
                            <button type="submit" class="btn btn-dark w-100 fw-bold" id="btnResetPassword">
                                Overwrite Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        $('#adminResetForm').on('submit', function(e) {
            e.preventDefault();
            
            let targetUserId = $('#target_user_id').val();
           let newPwd = $('#admin_new_password').val();
let confirmPwd = $('#admin_confirm_password').val();
            let $alertBox = $('#resetAlert');
            let $btn = $('#btnResetPassword');

            if (newPwd !== confirmPwd) {
                $alertBox.removeClass('d-none alert-success').addClass('alert alert-danger').html('<i class="bi bi-exclamation-triangle me-1"></i> Passwords do not match.');
                return;
            }

            $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...').prop('disabled', true);

            $.ajax({
                url: '../api/admin_reset_password.php',
                type: 'POST',
                data: JSON.stringify({
                    target_user_id: targetUserId,
                    new_password: newPwd
                }),
                contentType: 'application/json',
                success: function(response) {
                    $alertBox.removeClass('d-none alert-danger alert-success');
                    if (response.success) {
                        $alertBox.addClass('alert alert-success').html('<i class="bi bi-check-circle me-1"></i> ' + response.message);
                        $('#adminResetForm')[0].reset();
                    } else {
                        $alertBox.addClass('alert alert-danger').html('<i class="bi bi-exclamation-triangle me-1"></i> ' + response.message);
                    }
                },
                error: function() {
                    $alertBox.removeClass('d-none').addClass('alert alert-danger').html('<i class="bi bi-wifi-off me-1"></i> Server error.');
                },
                complete: function() {
                    $btn.html('Overwrite Password').prop('disabled', false);
                }
            });
        });
    });
    </script>
</body>
</html>
