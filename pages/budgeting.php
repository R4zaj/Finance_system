<?php
// pages/budgeting.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

// 1. ADDED: Connect to the database and fetch the new colleges!
require_once '../includes/db.php';

try {
    $deptStmt = $pdo->query("SELECT department_id, name FROM departments ORDER BY name ASC");
    $all_departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $all_departments = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Budget Management | Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; border-radius: 1rem; }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; }
    </style>
</head>
<body class="d-flex vh-100 bg-light">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <?php include '../includes/topheader.php'; ?>

        <main class="p-4 overflow-y-auto">
            
            <div class="card action-banner p-4 mb-4 border-0 shadow">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold"><i class="bi bi-wallet2 me-2"></i> Budget Management</h3>
                        <p class="mb-0 opacity-75">Allocate departmental funds and track encumbrances (reservations).</p>
                    </div>
                    <button class="btn btn-light fw-bold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#allocateModal">
                        <i class="bi bi-plus-circle me-2"></i> Allocate Budget
                    </button>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-start border-primary border-4">
                        <small class="text-muted text-uppercase fw-bold">Total Allocated</small>
                        <h4 id="tot-allocated" class="fw-bold">₱0.00</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-start border-warning border-4">
                        <small class="text-muted text-uppercase fw-bold">Total Reserved (POs)</small>
                        <h4 id="tot-reserved" class="fw-bold">₱0.00</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-start border-danger border-4">
                        <small class="text-muted text-uppercase fw-bold">Total Actual Spent</small>
                        <h4 id="tot-spent" class="fw-bold">₱0.00</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-custom p-3 border-start border-success border-4">
                        <small class="text-muted text-uppercase fw-bold">Total Available</small>
                        <h4 id="tot-available" class="fw-bold">₱0.00</h4>
                    </div>
                </div>
            </div>

            <div class="card card-custom shadow-sm p-0">
                <div class="card-header bg-white p-3 border-bottom">
                    <h6 class="fw-bold mb-0">Departmental Budget vs Actuals</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Department</th>
                                <th>Allocated</th>
                                <th>Reserved (Encumbered)</th>
                                <th>Actual Spent</th>
                                <th>Available Balance</th>
                                <th>Utilization</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="budgetTableBody">
                            <tr><td colspan="7" class="text-center py-4">Loading budgets...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div class="modal fade" id="allocateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content card-custom">
                <div class="modal-header border-0">
                    <h5 class="fw-bold">Set Department Allocation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="allocateForm">
                    <div class="modal-body bg-light">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Fiscal Year</label>
                            <input type="number" class="form-control" id="fiscal_year" value="<?= date('Y') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Department</label>
                            <select class="form-select" id="dept_id" required>
                                <option value="" selected disabled>Choose a college/department...</option>
                                
                                <?php foreach ($all_departments as $dept): ?>
                                    <option value="<?= htmlspecialchars($dept['department_id']) ?>">
                                        <?= htmlspecialchars($dept['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                                
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Allocated Amount (₱)</label>
                            <input type="number" step="0.01" min="1" class="form-control" id="allocated_amount" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="submit" class="btn btn-success w-100 fw-bold">Save Allocation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/budgeting.js"></script>
    <script> $(document).ready(function() {
    
    // Formatting function for Philippine Pesos
    const formatCurrency = (amount) => {
        return '₱' + parseFloat(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Load Data on Page Load
    function loadBudgetData() {
        $.ajax({
            url: '../api/get_budgets.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    
                    // 1. Update the Top 4 Summary Cards
                    $('#tot-allocated').text(formatCurrency(response.summary.allocated));
                    $('#tot-reserved').text(formatCurrency(response.summary.reserved));
                    $('#tot-spent').text(formatCurrency(response.summary.spent));
                    $('#tot-available').text(formatCurrency(response.summary.available));

                    // 2. Update the Department Table
                    let tableHTML = '';
                    response.departments.forEach(dept => {
                        
                        // Decide progress bar color based on utilization
                        let barColor = 'bg-success';
                        if (dept.utilization > 75) barColor = 'bg-warning';
                        if (dept.utilization > 90) barColor = 'bg-danger';

                        tableHTML += `
                            <tr>
                                <td class="fw-semibold text-dark">${dept.name}</td>
                                <td>${formatCurrency(dept.allocated)}</td>
                                <td class="text-warning fw-semibold">${formatCurrency(dept.reserved)}</td>
                                <td class="text-danger fw-semibold">${formatCurrency(dept.spent)}</td>
                                <td class="text-success fw-bold">${formatCurrency(dept.available)}</td>
                                <td style="width: 200px;">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>${dept.utilization.toFixed(1)}% Used</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar ${barColor}" style="width: ${dept.utilization}%"></div>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-light border" title="View Details"><i class="bi bi-eye"></i></button>
                                </td>
                            </tr>
                        `;
                    });

                    $('#budgetTableBody').html(tableHTML);
                } else {
                    $('#budgetTableBody').html(`<tr><td colspan="7" class="text-danger text-center">Error: ${response.message}</td></tr>`);
                }
            },
            error: function() {
                $('#budgetTableBody').html(`<tr><td colspan="7" class="text-danger text-center">Server connection failed.</td></tr>`);
            }
        });
    }

    // Initialize
    loadBudgetData();
});
    </script>
</body>
</html>
