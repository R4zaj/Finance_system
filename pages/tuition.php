<?php
// pages/tuition.php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit(); }

require_once '../includes/db.php';

// Fetch recent tuition collections
try {
    $stmt = $pdo->query("
        SELECT sp.payment_id, sp.pay_date as payment_date, sp.amount, sp.description, s.first_name, s.last_name 
        FROM student_payments sp
        LEFT JOIN students s ON sp.student_id = s.student_id
        ORDER BY sp.pay_date DESC 
        LIMIT 15
    ");
    $recent_collections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recent_collections = [];
}
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
            
           <div class="card action-banner p-4 mb-4 border-0 shadow-sm">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-wallet2 me-2"></i> Tuition & Fees</h3>
                        <p class="mb-0 text-white-50">Manage student accounts, record payments, and process enrollment clearances.</p>
                    </div>
                    
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
                                <th class="ps-4">Date</th>
                                <th>Student Name</th>
                                <th>Status</th>
                                <th>Description / Term</th>
                                <th class="text-end pe-4">Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody id="tuitionTableBody">
                            <?php if (empty($recent_collections)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">No recent collections found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_collections as $payment): ?>
                                    <tr>
                                        <td class="ps-4 text-muted small"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td class="fw-bold text-dark">
                                            <?= htmlspecialchars(trim($payment['first_name'] . ' ' . $payment['last_name'])) ?: 'LMS Enrollee' ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Completed</span>
                                        </td>
                                        <td>
                                            <?php if (strpos($payment['description'], 'Enrollment Approved') !== false): ?>
                                                <span class="fw-semibold text-success">
                                                    <i class="bi bi-check-circle me-1"></i> Enrollment Approved
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= htmlspecialchars($payment['description']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="fw-bold text-dark text-end pe-4">
                                            ₱<?= number_format($payment['amount'], 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

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
                                        <th>Program Details</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4" style="width: 250px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="pendingEnrollmentsBody">
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
    
    <script>
    $(document).ready(function() {
    
        // 1. Fetch pending enrollments from the external LMS
        $('#btnOpenApprovals').on('click', function() {
            loadPendingEnrollments();
        });

        function loadPendingEnrollments() {
            $('#pendingEnrollmentsBody').html('<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Syncing with LMS...</td></tr>');
            
            $.ajax({
                url: '../api/lms_api.php?action=get_pending',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    let html = '';
                    let studentData = response.data ? response.data : response;

                    if (studentData && studentData.length > 0) {
                        $.each(studentData, function(i, student) {
                            
                            let studentName = student.full_name;
                            let studentEmail = student.email || 'No Email Provided';
                            // We need the ID to map it locally
                            let localStudentId = student.student_id || student.id; 
                            
                            let enrollmentIds = [];
                            let courseCodes = [];
                            let totalUnits = 0;
                            let subjectCount = 0;

                            if (student.enrollments && student.enrollments.length > 0) {
                                subjectCount = student.enrollments.length;
                                $.each(student.enrollments, function(j, cls) {
                                    enrollmentIds.push(cls.enrollment_id); 
                                    courseCodes.push(cls.course_code);
                                    totalUnits += parseInt(cls.units) || 0;
                                });
                            }

                            let idsJson = JSON.stringify(enrollmentIds);

                            // UPDATED UI: We added a dynamic input box next to the approve button!
                            html += `
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark text-capitalize">${studentName}</div>
                                        <div class="small text-muted">${studentEmail}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary small"><i class="bi bi-journal-text me-1"></i>${subjectCount} Subjects (${totalUnits} Units)</div>
                                        <div class="text-muted" style="font-size: 0.70rem;">${courseCodes.join(', ')}</div>
                                    </td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end align-items-center gap-1">
                                            <div class="input-group input-group-sm" style="width: 140px;">
                                                <span class="input-group-text bg-light border-success text-success">₱</span>
                                                <input type="number" class="form-control border-success payment-amount-input" placeholder="Amount" value="5000" min="1">
                                            </div>
                                            <button class="btn btn-sm btn-success btn-approve" data-ids='${idsJson}' data-studentid='${localStudentId}'>
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center py-4 text-muted">No pending enrollments from the LMS to approve.</td></tr>';
                    }
                    
                    $('#pendingEnrollmentsBody').html(html);
                },
                error: function(xhr) {
                    console.error("LMS Sync Error:", xhr.responseText);
                    $('#pendingEnrollmentsBody').html('<tr><td colspan="4" class="text-center py-4 text-danger"><i class="bi bi-wifi-off me-2"></i>Failed to connect to the LMS API.</td></tr>');
                }
            });
        }

        // 2. Handle the "Approve" button click
        $(document).on('click', '.btn-approve', function() {
            let eIds = $(this).data('ids');
            let sId = $(this).data('studentid');
            
            // Look into this specific row and grab the amount the user typed!
            let amountPaid = $(this).closest('td').find('.payment-amount-input').val();
            
            if(!amountPaid || amountPaid <= 0) {
                alert("Please enter a valid tuition amount.");
                return;
            }

            let $btn = $(this);
            $btn.html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);

            $.ajax({
                url: '../api/lms_api.php?action=approve',
                type: 'POST',
                // We are now sending BOTH the enrollment IDs AND the collected amount!
                data: JSON.stringify({ 
                    enrollment_ids: eIds, 
                    student_id: sId,
                    amount: parseFloat(amountPaid) 
                }), 
                contentType: 'application/json',
                success: function(response) {
                    if (response.status === 'success' || response.success) {
                        
                        // 🚨 NEW: Check if the message contains the word "failed" and alert the user!
                        if (response.message && response.message.includes("failed")) {
                            alert("Warning: " + response.message);
                        }

                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                            location.reload(); 
                        });
                    } else {
                        alert('LMS Error: ' + (response.message || 'Approval failed.'));
                        $btn.html('<i class="bi bi-check-lg"></i>').prop('disabled', false);
                    }
                },
                error: function() {
                    alert('Server error while talking to the LMS API.');
                    $btn.html('<i class="bi bi-check-lg"></i>').prop('disabled', false);
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
