// assets/js/payroll.js

$(document).ready(function() {
    loadPayrollData();

    // Default the month picker to current month
    const today = new Date();
    const currentMonth = today.toISOString().substring(0, 7);
    $('#pay_period').val(currentMonth);

    $('#btnRunPayroll').on('click', function() {
        runPayroll();
    });
});

function loadPayrollData() {
    $('#previewTableBody').html('<tr><td colspan="5" class="text-center py-4">Loading employee data...</td></tr>');
    
    $.ajax({
        url: '../api/get_payroll_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderPreviewTable(response.employees);
                renderHistoryTable(response.history);
            } else {
                alert("Error loading data: " + response.message);
            }
        }
    });
}

function renderPreviewTable(employees) {
    const $tbody = $('#previewTableBody');
    $tbody.empty();
    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2});

    if (employees.length === 0) {
        $tbody.append('<tr><td colspan="5" class="text-center py-4">No active employees found.</td></tr>');
        return;
    }

    let totalPayroll = 0;
    $.each(employees, function(i, emp) {
        totalPayroll += parseFloat(emp.salary);
        $tbody.append(`
            <tr>
                <td class="fw-bold">${emp.first_name} ${emp.last_name}</td>
                <td class="text-muted">${emp.position}</td>
                <td class="text-end fw-bold">${fmt(emp.salary)}</td>
                <td class="text-end text-danger">-${fmt(emp.est_deductions)}</td>
                <td class="text-end text-success fw-bold">${fmt(emp.est_net_pay)}</td>
            </tr>
        `);
    });
    $('#estTotalPayroll').text(fmt(totalPayroll));
}

function renderHistoryTable(history) {
    const $tbody = $('#historyTableBody');
    $tbody.empty();
    
    // Helper function to safely format numbers and prevent "NaN"
    const fmt = (num) => {
        let parsed = parseFloat(num);
        if (isNaN(parsed)) return '₱0.00';
        return '₱' + parsed.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };

    if (history.length === 0) {
        $tbody.append('<tr><td colspan="5" class="text-center py-4">No payroll history found.</td></tr>');
        return;
    }

    $.each(history, function(i, p) {
        $tbody.append(`
            <tr>
                <td><span class="text-muted small">${p.pay_date}</span></td>
                <td class="fw-bold text-dark">${p.first_name} ${p.last_name}</td>
                <td class="text-end text-muted small">${fmt(p.gross_amount)}</td>
                <td class="text-end text-danger small">-${fmt(p.deductions)}</td>
                <td class="text-end text-success fw-bold">${fmt(p.net_amount)}</td>
            </tr>
        `);
    });

}

function runPayroll() {
    const period = $('#pay_period').val();
    if (!confirm(`Are you sure you want to execute the payroll run for ${period}? This will finalize payslips and post automatic journal entries to the Finance Ledger.`)) {
        return;
    }

    const $btn = $('#btnRunPayroll');
    $btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm me-2"></i>Processing...');

    $.ajax({
        url: '../api/process_payroll.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({ pay_period: period }),
        success: function(response) {
            $btn.prop('disabled', false).html('<i class="bi bi-play-circle me-2"></i>Execute Pay Run');
            if (response.success) {
                alert(response.message);
                loadPayrollData(); // Refresh history
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function() {
            $btn.prop('disabled', false).html('<i class="bi bi-play-circle me-2"></i>Execute Pay Run');
            alert("Network error while processing payroll.");
        }
    });
}