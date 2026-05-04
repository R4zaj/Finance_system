// assets/js/tuition.js

$(document).ready(function() {
    loadTuitionData();

    // Handle Payment Submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            student_id: $('#student_id').val(),
            amount: parseFloat($('#amount').val()),
            pay_date: $('#pay_date').val(),
            description: $('#description').val()
        };

        $.ajax({
            url: '../api/process_tuition_payment.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    $('#paymentModal').modal('hide');
                    $('#paymentForm')[0].reset();
                    
                    // Default to today for next time
                    document.getElementById('pay_date').valueAsDate = new Date();
                    
                    loadTuitionData(); // Refresh table
                } else {
                    alert("Error: " + response.message);
                }
            }
        });
    });
});

function loadTuitionData() {
    $.ajax({
        url: '../api/get_tuition_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderPaymentsTable(response.payments);
                populateStudentDropdown(response.students);
            }
        }
    });
}

function renderPaymentsTable(payments) {
    const $tbody = $('#tuitionTableBody');
    $tbody.empty();
    
    if (payments.length === 0) {
        $tbody.append('<tr><td colspan="5" class="text-center py-4">No payments found.</td></tr>');
        return;
    }

    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', { minimumFractionDigits: 2 });

    $.each(payments, function(i, p) {
        $tbody.append(`
            <tr>
                <td>${p.pay_date}</td>
                <td class="fw-bold">${p.last_name}, ${p.first_name}</td>
                <td><span class="badge bg-success bg-opacity-10 text-success px-2 py-1">Completed</span></td>
                <td>${p.description}</td>
                <td class="text-end fw-bold text-dark">${fmt(p.amount)}</td>
            </tr>
        `);
    });
}

function populateStudentDropdown(students) {
    const $select = $('#student_id');
    $select.empty().append('<option value="">-- Select Student --</option>');
    $.each(students, function(i, s) {
        $select.append(`<option value="${s.student_id}">${s.last_name}, ${s.first_name}</option>`);
    });
}
// Add this to the BOTTOM of your assets/js/tuition.js file

$(document).ready(function() {
    // When the Enrollment Clearance modal opens, fetch the pending students
    $('#enrollmentModal').on('show.bs.modal', function () {
        loadPendingEnrollments();
    });
});

function loadPendingEnrollments() {
    const $tbody = $('#pendingEnrollmentsBody');
    $tbody.html('<tr><td colspan="6" class="text-center py-4">Fetching enrollment data...</td></tr>');

    $.ajax({
        url: '../api/enrollment_api.php',
        type: 'GET',
        data: { action: 'get_pending' },
        dataType: 'json',
        success: function(response) {
            $tbody.empty();
            if (response.status === 'success' && response.data.length > 0) {
                const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2});
                
                $.each(response.data, function(i, req) {
                    $tbody.append(`
                        <tr>
                            <td class="fw-bold text-primary">${req.enrollment_id}</td>
                            <td>
                                <div class="fw-bold">${req.student_name}</div>
                                <small class="text-muted">ID: ${req.student_id}</small>
                            </td>
                            <td>
                                <div>${req.program}</div>
                                <small class="text-muted">${req.year_level}</small>
                            </td>
                            <td class="fw-bold text-end">${fmt(req.total_assessment)}</td>
                            <td><span class="badge bg-warning text-dark"><i class="bi bi-clock-history me-1"></i> Pending</span></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-success shadow-sm fw-bold" onclick="approveEnrollment('${req.enrollment_id}', '${req.student_name}')">
                                    <i class="bi bi-check-circle me-1"></i> Approve
                                </button>
                            </td>
                        </tr>
                    `);
                });
            } else {
                $tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">No pending enrollments require finance clearance.</td></tr>');
            }
        },
        error: function() {
            $tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to connect to Enrollment API.</td></tr>');
        }
    });
}

function approveEnrollment(enrollment_id, student_name) {
    if (!confirm(`Are you sure you want to approve the enrollment for ${student_name}? This will clear them for the semester.`)) {
        return;
    }

    $.ajax({
        url: '../api/enrollment_api.php',
        type: 'POST',
        data: { 
            action: 'approve', 
            enrollment_id: enrollment_id 
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                loadPendingEnrollments(); // Refresh the modal table
                loadTuitionData(); // Refresh main dashboard stats if needed
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function() {
            alert("Network error while processing approval.");
        }
    });
}