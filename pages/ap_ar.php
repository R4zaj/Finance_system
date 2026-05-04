<?php
// pages/ap_ar.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>AP & AR | Finance System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-custom { border: none; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .action-banner { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; border-radius: 1rem; }
        .table-custom th { font-size: 0.75rem; text-transform: uppercase; color: #6c757d; border-bottom: 2px solid #dee2e6; }
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
                        <h3 class="fw-bold"><i class="bi bi-arrow-left-right me-2"></i> Payables & Receivables</h3>
                        <p class="mb-0 opacity-75">Manage vendor disbursements (AP) and track collection summaries (AR).</p>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="card card-custom p-4 border-start border-danger border-4 h-100">
                        <small class="text-muted text-uppercase fw-bold">Total Accounts Payable (Due)</small>
                        <h2 id="stat-ap-due" class="fw-bold text-danger mt-2">₱0.00</h2>
                        <p class="small text-muted mb-0">Total unpaid balance across all outstanding Purchase Orders.</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-custom p-4 border-start border-success border-4 h-100">
                        <small class="text-muted text-uppercase fw-bold">YTD Accounts Receivable (Collected)</small>
                        <h2 id="stat-ar-collected" class="fw-bold text-success mt-2">₱0.00</h2>
                        <p class="small text-muted mb-0">Total student tuition payments received this year.</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Accounts Payable Table -->
                <div class="col-xl-8">
                    <div class="card card-custom shadow-sm p-0 h-100">
                        <div class="card-header bg-white p-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-danger"><i class="bi bi-cart-x me-2"></i>Outstanding Payables (AP)</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom align-middle mb-0 bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th>PO Ref</th>
                                        <th>Supplier Name</th>
                                        <th>Order Date</th>
                                        <th>Total PO Amount</th>
                                        <th>Balance Due</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="apOutstandingBody">
                                    <tr><td colspan="6" class="text-center py-4">Loading AP data...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Vendor Payments History -->
                <div class="col-xl-4">
                    <div class="card card-custom shadow-sm p-0 h-100">
                        <div class="card-header bg-white p-3 border-bottom">
                            <h6 class="fw-bold mb-0 text-success"><i class="bi bi-clock-history me-2"></i>Recent AP Payments</h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-custom align-middle mb-0 bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>PO</th>
                                        <th>Supplier</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="apHistoryBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Process Vendor Payment Modal -->
    <div class="modal fade" id="apPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content card-custom">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Process Vendor Disbursement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="apPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" id="pay_po_id">
                        <input type="hidden" id="pay_supplier_id">
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Supplier & Reference</label>
                            <input type="text" class="form-control bg-light" id="display_supplier" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Payment Date</label>
                            <input type="date" class="form-control bg-light" id="pay_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount to Pay (₱)</label>
                            <input type="number" step="0.01" min="1" class="form-control bg-light" id="pay_amount" required>
                            <small class="text-muted">You can process partial payments.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: #1a5632;">Record Payment & Update Ledger</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>document.getElementById('pay_date').valueAsDate = new Date();</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/ap_ar.js"></script>
</body>
</html>