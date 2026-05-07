<?php
// pages/procurement.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Procurement & Inventory | Finance System</title>
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
            
            <!-- Top Controls Banner -->
            <div class="card action-banner p-4 mb-4 border-0 shadow-sm" style="color: #ffffff !important;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-cart3 me-2"></i> Procurement</h3>
                        <p class="mb-0 fw-medium opacity-75">Review purchase orders, approve expenses, and manage vendor supplies.</p>
                    </div>
                    
                    <!-- Button to open the Pending POs Modal -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-dark fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#poApprovalModal">
                            <i class="bi bi-clipboard-check me-1"></i> Review Pending POs
                        </button>
                    </div>
                </div>
            </div>

            <!-- Main Page Content: Processed PO History -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-custom p-0 shadow-sm h-100">
                        <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history me-2"></i>Processed Purchase Orders</h6>
                        </div>
                        <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                            <table class="table table-hover table-custom align-middle mb-0 bg-white">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="small text-muted text-uppercase">PO Number</th>
                                        <th class="small text-muted text-uppercase">Supplier</th>
                                        <th class="small text-muted text-uppercase">Order Date</th>
                                        <th class="small text-muted text-uppercase text-end">Total Amount</th>
                                        <th class="small text-muted text-uppercase">Final Status</th>
                                    </tr>
                                </thead>
                                <tbody id="poHistoryBody">
                                    <!-- AJAX will load history here on page load -->
                                    <tr><td colspan="5" class="text-center py-4">Loading PO history...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- PO Approval Modal (Hidden by default) -->
    <div class="modal fade" id="poApprovalModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content card-custom">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-clipboard-check text-success me-2"></i>Finance PO Approval</h5>
                        <small class="text-muted">Review pending Purchase Orders and approve them for payment/inventory receipt.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light mt-3 p-4">
                    <div class="card card-custom border-0 shadow-sm p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="small text-muted text-uppercase">PO Number</th>
                                        <th class="small text-muted text-uppercase">Supplier</th>
                                        <th class="small text-muted text-uppercase">Order Date</th>
                                        <th class="small text-muted text-uppercase text-end">Total Amount</th>
                                        <th class="small text-muted text-uppercase">Status</th>
                                        <th class="small text-muted text-uppercase text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingPOsBody">
                                    <!-- AJAX will load pending POs here when modal opens -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Cache buster attached to force browser to load new JS -->
    <script src="../assets/js/procurement.js?v=<?php echo time(); ?>"></script>
</body>
</html>
