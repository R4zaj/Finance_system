// assets/js/dashboard.js

$(document).ready(function() {
    // Initial load for the current year
    const currentYear = new Date().getFullYear();
    loadDashboard(currentYear);

    // Handle Year Selection if you have a dropdown
    $('#yearSelect').on('change', function() {
        loadDashboard($(this).val());
    });
});

function loadDashboard(year) {
    $.ajax({
        url: '../api/get_dashboard_data.php', // Path to your provided PHP code
        type: 'GET',
        data: { year: year },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderSummary(response.summary);
                renderRecentTransactions(response.recent_transactions);
                
                // If you use Chart.js, pass response.monthly_data and response.budget_by_department here
                console.log("Monthly Data for Charts:", response.monthly_data);
            }
        },
        error: function(xhr) {
            console.error("Dashboard API Error:", xhr.responseText);
        }
    });
}

function renderSummary(summary) {
    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    // Mapping API keys to your UI IDs
    $('#stat-budget').text(fmt(summary.total_budget));
    $('#stat-expenses').text(fmt(summary.total_expenses));
    $('#stat-revenue').text(fmt(summary.total_revenue));
    $('#stat-student-payments').text(fmt(summary.total_student_payments));
    
    // Calculate Net Position (Revenue - Expenses)
    const net = summary.total_revenue - summary.total_expenses;
    $('#stat-net').text(fmt(net));
}

function renderRecentTransactions(transactions) {
    const $tableBody = $('#recentTransactionsTable');
    $tableBody.empty();

    if (transactions.length === 0) {
        $tableBody.append('<tr><td colspan="5" class="text-center text-muted">No transactions for this year.</td></tr>');
        return;
    }

    $.each(transactions, function(i, t) {
        const badge = t.type === 'Credit' ? 'bg-success' : 'bg-danger';
        $tableBody.append(`
            <tr>
                <td>${t.trans_date}</td>
                <td><span class="fw-bold">${t.account_name}</span></td>
                <td><span class="badge ${badge} bg-opacity-10 text-${t.type === 'Credit' ? 'success' : 'danger'} border px-2 py-1">${t.type}</span></td>
                <td>₱${parseFloat(t.amount).toLocaleString()}</td>
                <td><small class="text-muted">${t.description}</small></td>
            </tr>
        `);
    });
}