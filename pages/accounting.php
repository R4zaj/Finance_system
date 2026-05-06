<?php
// pages/accounting.php
session_start();

// Security Check: Redirect to the root login page if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting & Ledgers | Finance System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', system-ui, sans-serif; }
        
        .overflow-y-auto { scroll-behavior: smooth; }

        /* Card Customizations */
        .card-custom { 
            border: none; 
            border-radius: 1rem; 
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        /* Top Action Bar Gradient */
        .action-banner {
            background: linear-gradient(135deg, #114227 0%, #1a5632 100%);
            color: white;
            border-radius: 1rem;
        }
        
        /* Table Styling */
        .table-custom th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
        }
        .table-custom td {
            vertical-align: middle;
            padding: 1rem;
            color: #495057;
            font-weight: 500;
        }
        
        /* Account Type Badges */
        .badge-asset { background-color: rgba(25, 135, 84, 0.1); color: #198754; }
        .badge-liability { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; }
        .badge-equity { background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        .badge-revenue { background-color: rgba(13, 202, 240, 0.1); color: #087990; }
        .badge-expense { background-color: rgba(253, 126, 20, 0.1); color: #fd7e14; }
    </style>
</head>
<body class="d-flex h-100 vh-100 overflow-hidden">

    <!-- Sidebar Module -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content Wrapper -->
    <div class="flex-grow-1 d-flex flex-column h-100" style="min-width: 0;">
        
        <!-- Top Header Module -->
        <?php include '../includes/topheader.php'; ?>

        <!-- Scrollable Main Content -->
        <main class="p-3 p-md-4 overflow-y-auto bg-light" style="height: calc(100vh - 70px);">
            
            <!-- Page Header & Actions -->
           <!-- Page Header & Actions -->
            <div class="card border-0 mb-4 action-banner shadow-sm">
                <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-journal-text me-2"></i> Accounting & Ledgers</h3>
                        <p class="mb-0 text-white-50">Manage your Chart of Accounts and view recent General Ledger entries.</p>
                    </div>
                    <!-- We wrap the buttons in a flex container so they sit next to each other -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-light fw-semibold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#newEntryModal">
                            <i class="bi bi-plus-lg me-1"></i> New Entry
                        </button>
                        <button class="btn btn-outline-light fw-semibold" id="btnExport">
                            <i class="bi bi-download me-1"></i> Export
                        </button>
                    </div>
                </div> <!-- Closes card-body -->
            </div> <!-- Closes the green action-banner -->

            <!-- Main Content Area -->
            <div class="row g-4">
                
                <!-- Recent Journal Entries Table -->
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-3 ps-2">
                        <h6 class="fw-bold text-muted text-uppercase mb-0" style="font-size: 0.8rem; letter-spacing: 1px;">General Ledger History</h6>
                        
                        <div class="input-group w-auto">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search entries...">
                        </div>
                    </div>

                    <div class="card card-custom overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover table-custom mb-0 bg-white align-middle">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Account</th>
                                        <th>Type</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit (₱)</th>
                                        <th class="text-end">Credit (₱)</th>
                                    </tr>
                                </thead>
                                <tbody id="ledgerTableBody">
                                    <!-- AJAX will load data here -->
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-success spinner-border-sm me-2" role="status"></div>
                                            Initializing ledger...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer bg-white border-top-0 p-3 text-center">
                            <button class="btn btn-link text-success fw-semibold text-decoration-none p-0">
                                View Full History <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Modals Module -->
    <?php include '../includes/modals.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/auth.js"></script>
    <script src="../assets/js/ledger.js"></script>
</body>
</html>
