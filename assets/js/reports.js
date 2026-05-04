// assets/js/reports.js

$(document).ready(function() {
    const currentYear = new Date().getFullYear();
    $('#reportYear').val(currentYear);
    loadReports(currentYear);

    // Filter by Year
    $('#reportYear').on('change', function() {
        loadReports($(this).val());
    });

    // Print Report
    $('#printBtn').on('click', function() {
        window.print();
    });
});

function loadReports(year) {
    $.ajax({
        url: '../api/get_financial_reports.php',
        type: 'GET',
        data: { year: year },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderIncomeStatement(response.data.income_statement);
                renderBalanceSheet(response.data.balance_sheet);
                $('#displayYear').text(year);
            }
        }
    });
}

function fmt(num) {
    return '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

function renderIncomeStatement(is) {
    const $tbody = $('#incomeStatementBody');
    $tbody.empty();

    // Revenues
    $tbody.append('<tr class="bg-light"><td colspan="2" class="fw-bold text-success">Revenues</td></tr>');
    if(is.revenue.length === 0) $tbody.append('<tr><td colspan="2" class="text-muted fst-italic">No revenue recorded</td></tr>');
    $.each(is.revenue, (i, r) => $tbody.append(`<tr><td class="ps-4">${r.name}</td><td class="text-end">${fmt(r.balance)}</td></tr>`));
    $tbody.append(`<tr><td class="fw-bold text-end">Total Revenue:</td><td class="fw-bold text-end border-top">${fmt(is.total_revenue)}</td></tr>`);

    // Expenses
    $tbody.append('<tr class="bg-light"><td colspan="2" class="fw-bold text-danger mt-3">Expenses</td></tr>');
    if(is.expense.length === 0) $tbody.append('<tr><td colspan="2" class="text-muted fst-italic">No expenses recorded</td></tr>');
    $.each(is.expense, (i, e) => $tbody.append(`<tr><td class="ps-4">${e.name}</td><td class="text-end">${fmt(e.balance)}</td></tr>`));
    $tbody.append(`<tr><td class="fw-bold text-end">Total Expenses:</td><td class="fw-bold text-end border-top">${fmt(is.total_expense)}</td></tr>`);

    // Net Income
    const netColor = is.net_income >= 0 ? 'text-success' : 'text-danger';
    $tbody.append(`
        <tr class="table-active">
            <td class="fw-bolder fs-5">NET INCOME</td>
            <td class="fw-bolder fs-5 text-end ${netColor} border-top border-dark border-2">${fmt(is.net_income)}</td>
        </tr>
    `);
}

function renderBalanceSheet(bs) {
    const $tbody = $('#balanceSheetBody');
    $tbody.empty();

    // Assets
    $tbody.append('<tr class="bg-light"><td colspan="2" class="fw-bold text-primary">Assets</td></tr>');
    if(bs.asset.length === 0) $tbody.append('<tr><td colspan="2" class="text-muted fst-italic">No assets recorded</td></tr>');
    $.each(bs.asset, (i, a) => $tbody.append(`<tr><td class="ps-4">${a.name}</td><td class="text-end">${fmt(a.balance)}</td></tr>`));
    $tbody.append(`<tr><td class="fw-bold text-end">Total Assets:</td><td class="fw-bold text-end border-top">${fmt(bs.total_asset)}</td></tr>`);

    // Liabilities
    $tbody.append('<tr class="bg-light"><td colspan="2" class="fw-bold text-warning mt-3">Liabilities</td></tr>');
    if(bs.liability.length === 0) $tbody.append('<tr><td colspan="2" class="text-muted fst-italic">No liabilities recorded</td></tr>');
    $.each(bs.liability, (i, l) => $tbody.append(`<tr><td class="ps-4">${l.name}</td><td class="text-end">${fmt(l.balance)}</td></tr>`));
    $tbody.append(`<tr><td class="fw-bold text-end">Total Liabilities:</td><td class="fw-bold text-end border-top">${fmt(bs.total_liability)}</td></tr>`);
}