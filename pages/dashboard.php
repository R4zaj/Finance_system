<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PLSP Finance | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card-stat { border: none; border-radius: 1rem; transition: transform 0.2s; }
        .card-stat:hover { transform: translateY(-5px); }
        .hero-green { background: linear-gradient(135deg, #114227 0%, #1a5632 100%); color: white; }
    </style>
</head>
<body class="d-flex vh-100 bg-light">

    <?php include '../includes/sidebar.php'; ?>

    <div class="flex-grow-1 d-flex flex-column overflow-hidden">
        <?php include '../includes/topheader.php'; ?>

        <main class="p-4 overflow-y-auto">
            <!-- Hero Section -->
            <div class="card hero-green p-4 mb-4 border-0 shadow">
                <h2 class="fw-bold"> Dashboard</h2>
                <p class="opacity-75 mb-0">Consolidated platform uniting Student Info and Business Operations.</p>
            </div>

            <!-- Financial Cards Row -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card card-stat p-3 shadow-sm border-start border-primary border-4">
                        <small class="text-muted text-uppercase fw-bold">Total Budget</small>
                        <h3 id="stat-budget" class="fw-bold">₱0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat p-3 shadow-sm border-start border-danger border-4">
                        <small class="text-muted text-uppercase fw-bold">Actual Expenses</small>
                        <h3 id="stat-expenses" class="fw-bold">₱0.00</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-stat p-3 shadow-sm border-start border-warning border-4">
                        <small class="text-muted text-uppercase fw-bold">Pending POs</small>
                        <h3 id="stat-orders" class="fw-bold">0</h3>
                    </div>
                </div>
            
            </div>

            <!-- Integrated Modules Grid -->
            <div class="row g-4">
                <!-- Left Column: Activity & Transactions (Stacked) -->
                <div class="col-lg-8 d-flex flex-column gap-4">
                    
                    <!-- 1. Recent System Activity (Top) -->
                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-activity text-success me-2"></i>Recent System Activity</h5>
                        <div class="small text-muted d-flex align-items-center gap-3">
                            <div class="spinner-grow spinner-grow-sm text-success" role="status"></div>
                            <span>Real-time sync active: HRIS, Supply, and Scheduling modules are up to date.</span>
                        </div>
                    </div>

                    <!-- 2. Recent Transactions (Bottom) -->
                    <!-- 2. Recent Transactions (Bottom) -->
                    <div class="card border-0 shadow-sm flex-grow-1 p-0">
                        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Recent Financial Transactions</h5>
                        </div>
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover align-middle mb-0 bg-white">
                                <thead class="bg-light sticky-top">
                                    <tr>
                                        <th class="small text-muted text-uppercase">Date</th>
                                        <th class="small text-muted text-uppercase">Account</th>
                                        <th class="small text-muted text-uppercase">Type</th>
                                        <th class="small text-muted text-uppercase">Amount</th>
                                        <th class="small text-muted text-uppercase">Description</th>
                                    </tr>
                                </thead>
                                <tbody id="recentTransactionsTable">
                                    <tr><td colspan="5" class="text-center py-4">Loading ledger entries...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                                        
                </div>

                <!-- Right Column: Net Position -->
                <div class="col-lg-4">
                    <div class="card border-0 hero-green p-4 h-100 shadow">
                        <h5 class="fw-bold">Net Financial Position</h5>
                        <h2 id="stat-net" class="mt-3">₱0.00</h2>
                        <p class="small opacity-75 mt-auto">Automatically updated via double-entry journal entries from all integrated systems.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

            </thead>
            <tbody id="recentTransactionsTable">
                <!-- AJAX will load data here -->
            </tbody>
        </table>
    </div>
</div>

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Dash Script -->
    <script src="../assets/js/dashboard.js"></script>
</body>
</html>
