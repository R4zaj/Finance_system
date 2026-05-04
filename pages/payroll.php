<?php
// pages/payroll.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Payroll & Benefits | Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; border-radius: 1rem; }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; border-bottom: 2px solid #dee2e6; }
    </style>
</head>
<body class="d-flex vh-100 overflow-hidden">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column h-100">
        <?php include '../includes/topheader.php'; ?>

        <main class="p-4 overflow-y-auto bg-light">
            
            <!-- Banner -->
            <div class="card action-banner p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-cash-stack me-2"></i> HR Payroll Management</h3>
                        <p class="mb-0 text-white-50">Review employee deductions, process salary disbursements, and auto-sync with the General Ledger.</p>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 bg-white bg-opacity-10 p-2 rounded">
                        <input type="month" id="pay_period" class="form-control bg-light border-0 shadow-none">
                        <button id="btnRunPayroll" class="btn btn-light fw-bold text-primary shadow-sm text-nowrap">
                            <i class="bi bi-play-circle me-2"></i>Execute Pay Run
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Preview List -->
                <div class="col-lg-7">
                    <div class="card card-custom h-100 p-0">
                        <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark">Current Roster Preview</h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary">Est. Gross: <span id="estTotalPayroll">₱0.00</span></span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom align-middle mb-0 bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Position</th>
                                        <th class="text-end">Base Salary</th>
                                        <th class="text-end">Est. Deductions</th>
                                        <th class="text-end">Est. Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- History List -->
                <!-- History List -->
                <div class="col-lg-5">
                    <div class="card card-custom h-100 p-0">
                        <div class="card-header bg-white p-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-dark">Recent Payslip History</h6>
                        </div>
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-hover table-custom align-middle mb-0 bg-white">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th>Date</th>
                                        <th>Employee</th>
                                        <th class="text-end">Gross</th>
                                        <th class="text-end">Deductions</th>
                                        <th class="text-end">Net Paid</th>
                                    </tr>
                                </thead>
                                <tbody id="historyTableBody">
                                    <!-- Populated by JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/payroll.js"></script>
</body>
</html>