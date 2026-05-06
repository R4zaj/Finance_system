<?php
// pages/tuition.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tuition & Fees | Finance System</title>
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
            
           <!-- Top Controls Banner -->
            <div class="card action-banner p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-wallet2 me-2"></i> Tuition & Fees</h3>
                        <p class="mb-0 text-white-50">Manage student accounts, record payments, and process enrollment clearances.</p>
                    </div>
                    
                    <!-- Buttons placed clearly in the banner -->
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#approvalModal" id="btnOpenApprovals">
                            <i class="bi bi-person-check me-1"></i> Enrollment Approval
                        </button>
                        
                        <button class="btn btn-light fw-bold text-success shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="bi bi-plus-lg me-1"></i> Record Payment
                        </button>
                    </div>
                </div>
            </div>

            <!-- Recent Payments Table -->
            <div class="card card-custom shadow-sm p-0">
                <div class="card-header bg-white p-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Recent Tuition Collections</h6>
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search student...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-custom align-middle mb-0 bg-white">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Description / Term</th>
                                <th class="text-end">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody id="tuitionTableBody">
                            <tr><td colspan="5" class="text-center py-4">Loading data...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Record Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content card-custom">
                <div class="modal-header border-0 pb-0">
                    <h5 class="fw-bold">Process Student Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="paymentForm">
                    <div class="modal-body">
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 small">
                            <i class="bi bi-info-circle me-1"></i> Submitting this form will automatically update the General Ledger.
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Student</label>
                            <select class="form-select bg-light" id="student_id" required>
                                <!-- AJAX will populate this -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Payment Date</label>
                            <input type="date" class="form-control bg-light" id="pay_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount Received (₱)</label>
                            <input type="number" step="0.01" min="1" class="form-control bg-light" id="amount" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Description / Term</label>
                            <input type="text" class="form-control bg-light" id="description" placeholder="e.g. 1st Semester Tuition" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-success w-100 fw-bold" style="background-color: #1a5632;">Process Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Enrollment Clearance Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content card-custom">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="fw-bold mb-0"><i class="bi bi-shield-check text-warning me-2"></i>Finance Clearance for Enrollment</h5>
                        <small class="text-muted">Review student assessments and grant official financial approval for the term.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light mt-3 p-4">
                    <div class="card card-custom border-0 shadow-sm p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 bg-white">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Student Name</th>
                                        <th>Email Address</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingEnrollmentsBody">
                                    <!-- AJAX will load pending students here -->
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

    <!-- 1. External Libraries First -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- 2. External Custom Scripts -->
    <script src="../assets/js/tuition.js"></script>

    <!-- 3. Inline Logic -->
    <script>
    $(document).ready(function() {
        // Load pending enrollments when the button is clicked
        $('#btnOpenApprovals').on('click', function() {
            loadPendingEnrollments();
        });

        function loadPendingEnrollments() {
            $('#pendingEnrollmentsBody').html('<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Loading...</td></tr>');
            
            $.ajax({
                url: '../api/get_pending_enrollments.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    if (response.success && response.data.length > 0) {
                        $.each(response.data, function(i, student) {
                            html += `
                                <tr>
                                    <td class="ps-4 fw-semibold">${student.first_name} ${student.last_name}</td>
                                    <td class="text-muted small">${student.email}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-success btn-approve" data-id="${student.student_id}">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center py-4 text-muted">No pending enrollments to approve.</td></tr>';
                    }
                    $('#pendingEnrollmentsBody').html(html);
                }
            });
        }

        // Handle the "Approve" button click inside the modal
        $(document).on('click', '.btn-approve', function() {
            let studentId = $(this).data('id');
            let $btn = $(this);
            
            $btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

            $.ajax({
                url: '../api/approve_enrollment.php',
                type: 'POST',
                data: JSON.stringify({ student_id: studentId }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        // Remove the row from the table with a nice fade effect
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            // If table is empty, show the "No pending" message
                            if ($('#pendingEnrollmentsBody tr').length === 0) {
                                $('#pendingEnrollmentsBody').html('<tr><td colspan="4" class="text-center py-4 text-muted">No pending enrollments to approve.</td></tr>');
                            }
                        });
                    } else {
                        alert('Error: ' + response.message);
                        $btn.html('<i class="bi bi-check-lg"></i> Approve').prop('disabled', false);
                    }
                }
            });
        });
    });

    // Initialization Script for Date Input
    let payDateInput = document.getElementById('pay_date');
    if (payDateInput) {
        payDateInput.valueAsDate = new Date();
    }
    </script>
</body>
</html>
