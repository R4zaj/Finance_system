<header class="bg-white border-bottom px-3 px-md-4 py-3 d-flex justify-content-between align-items-center shadow-sm" style="min-height: 70px; z-index: 1020;">
    <div class="d-flex align-items-center">
        <button class="btn btn-link text-dark d-lg-none me-2 p-0" id="toggleSidebar">
            <i class="bi bi-list fs-3"></i>
        </button>
        <i class="bi bi-bank fs-4 me-2" style="color: #144d32;"></i>
        <h5 class="mb-0 fw-bold d-none d-sm-block" style="color: #144d32;">Finance Management System</h5>
        <h5 class="mb-0 fw-bold d-sm-none" style="color: #144d32;">FMS</h5>
    </div>

    <div class="d-flex align-items-center gap-3 gap-md-4">
        <div class="d-flex align-items-center d-none d-md-flex">
            <span class="spinner-grow spinner-grow-sm text-success me-2" role="status" style="width: 8px; height: 8px;"></span>
            <small class="text-muted fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.5px;">SYSTEM OPERATIONAL</small>
        </div>

        <div class="dropdown">
            <button class="btn btn-sm rounded-pill border-0 d-flex align-items-center px-3 py-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #e8f5e9; color: #144d32; font-weight: 600; transition: transform 0.2s;">
                <i class="bi bi-person-circle me-2 fs-5"></i>
                <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'admin'; ?>
                <i class="bi bi-chevron-down ms-2" style="font-size: 0.7rem;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 rounded-3">
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#settingsModal"><i class="bi bi-gear me-2 text-muted"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2 text-danger" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right me-2"></i> Sign out</a></li>
            </ul>
        </div>
    </div>
</header>

<div class="modal fade" id="settingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-gear text-primary me-2"></i>Account Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <ul class="nav nav-tabs border-bottom mb-3" id="settingsTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-semibold" data-bs-toggle="tab" data-bs-target="#securityTab" type="button" role="tab">Change Password</button>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade show active" id="securityTab" role="tabpanel">
                        <form id="changePasswordForm">
                            <div id="passwordAlert" class="alert d-none small py-2"></div>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Current Password</label>
                                <input type="password" class="form-control bg-light" id="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">New Password</label>
                                <input type="password" class="form-control bg-light" id="new_password" required minlength="6">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Confirm New Password</label>
                                <input type="password" class="form-control bg-light" id="confirm_password" required minlength="6">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold mt-2" id="btnSavePassword">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center mt-3">
                <p class="mb-0">Are you sure you want to sign out of the Finance System?</p>
            </div>
            <div class="modal-footer border-0 justify-content-center pt-0 pb-3">
                <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <a href="../login.php" class="btn btn-danger btn-sm fw-bold px-4">Yes, Sign Out</a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const pwdForm = document.getElementById('changePasswordForm');
    if (pwdForm) {
        pwdForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            let currentPwd = document.getElementById('current_password').value;
            let newPwd = document.getElementById('new_password').value;
            let confirmPwd = document.getElementById('confirm_password').value;
            let alertBox = document.getElementById('passwordAlert');
            let btn = document.getElementById('btnSavePassword');

            // Basic Validation
            if (newPwd !== confirmPwd) {
                alertBox.className = 'alert alert-danger small py-2';
                alertBox.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> New passwords do not match!';
                return;
            }

            // UI Loading State
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';
            btn.disabled = true;

            // Send to backend
            fetch('../api/update_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    current_password: currentPwd,
                    new_password: newPwd
                })
            })
            .then(response => response.json())
            .then(data => {
                alertBox.classList.remove('d-none');
                if (data.success) {
                    alertBox.className = 'alert alert-success small py-2';
                    alertBox.innerHTML = '<i class="bi bi-check-circle me-1"></i> ' + data.message;
                    pwdForm.reset();
                } else {
                    alertBox.className = 'alert alert-danger small py-2';
                    alertBox.innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> ' + data.message;
                }
            })
            .catch(error => {
                alertBox.className = 'alert alert-danger small py-2';
                alertBox.innerHTML = '<i class="bi bi-wifi-off me-1"></i> Server connection error.';
            })
            .finally(() => {
                btn.innerHTML = 'Update Password';
                btn.disabled = false;
            });
        });
    }
});
</script>
