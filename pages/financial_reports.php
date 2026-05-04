<?php
// pages/financial_reports.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Reports | Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; border-radius: 1rem; }
        
        /* Print Styles to ensure clean PDF exports */
        @media print {
            body { background: white !important; }
            .d-print-none { display: none !important; }
            .card-custom { box-shadow: none !important; border: 1px solid #ddd !important; border-radius: 0 !important; }
            .print-container { padding: 0 !important; margin: 0 !important; width: 100% !important; }
            table { font-size: 14px; }
        }
    </style>
</head>
<body class="d-flex h-100 vh-100 bg-light">

    <!-- Sidebar hidden on print -->
    <div class="d-print-none h-100">
        <?php include '../includes/sidebar.php'; ?>
    </div>

    <div class="flex-grow-1 d-flex flex-column h-100 overflow-hidden print-container">
        <div class="d-print-none">
            <?php include '../includes/topheader.php'; ?>
        </div>

        <main class="p-4 overflow-y-auto print-container">
            
            <!-- Controls (Hidden on Print) -->
            <div class="card action-banner p-4 mb-4 border-0 shadow d-print-none">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="fw-bold"><i class="bi bi-file-earmark-bar-graph me-2"></i> Financial Reports</h3>
                        <p class="mb-0 opacity-75">Generate Income Statements and Balance Sheets for auditing.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <select id="reportYear" class="form-select fw-bold shadow-sm">
                            <option value="2026">2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                        <button id="printBtn" class="btn btn-light fw-bold text-success shadow-sm">
                            <i class="bi bi-printer me-2"></i> Print / PDF
                        </button>
                    </div>
                </div>
            </div>

            <!-- Print Header (Visible only on Print) -->
            <div class="d-none d-print-block mb-4 text-center">
                <h3 class="fw-bold mb-0">Pamantasan ng Lungsod ng San Pablo</h3>
                <h5 class="text-muted">Consolidated Financial Reports - Fiscal Year <span id="displayYear"></span></h5>
                <hr>
            </div>

            <div class="row g-4">
                <!-- Income Statement -->
                <div class="col-lg-6">
                    <div class="card card-custom p-0 h-100">
                        <div class="card-header bg-white p-3 border-bottom text-center">
                            <h5 class="fw-bold mb-0 text-dark">Income Statement</h5>
                            <small class="text-muted">For the Year Ended Dec 31</small>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <tbody id="incomeStatementBody">
                                    <!-- AJAX Content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Balance Sheet -->
                <div class="col-lg-6">
                    <div class="card card-custom p-0 h-100">
                        <div class="card-header bg-white p-3 border-bottom text-center">
                            <h5 class="fw-bold mb-0 text-dark">Balance Sheet</h5>
                            <small class="text-muted">As of Dec 31</small>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <tbody id="balanceSheetBody">
                                    <!-- AJAX Content -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Signature line for prints -->
            <div class="d-none d-print-block mt-5 pt-5">
                <div class="row">
                    <div class="col-4 text-center">
                        <hr class="border-dark">
                        <p class="mb-0 fw-bold">Prepared By</p>
                        <small>Finance Department</small>
                    </div>
                    <div class="col-4"></div>
                    <div class="col-4 text-center">
                        <hr class="border-dark">
                        <p class="mb-0 fw-bold">Approved By</p>
                        <small>University Auditor</small>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/reports.js"></script>
</body>
</html>