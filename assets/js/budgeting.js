// assets/js/budgeting.js

$(document).ready(function() {
    const currentYear = new Date().getFullYear();
    loadBudgets(currentYear);

    // Handle Budget Allocation Form Submit
    $('#allocateForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            department_id: $('#dept_id').val(),
            year: $('#fiscal_year').val(),
            amount: parseFloat($('#allocated_amount').val())
        };

        $.ajax({
            url: '../api/allocate_budget.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    $('#allocateModal').modal('hide');
                    loadBudgets($('#fiscal_year').val());
                    $('#allocateForm')[0].reset();
                } else {
                    alert("Error: " + response.message);
                }
            }
        });
    });
});

function loadBudgets(year) {
    $.ajax({
        url: '../api/get_budgets.php',
        type: 'GET',
        data: { year: year },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderBudgetTable(response.data);
            }
        }
    });
}

function renderBudgetTable(departments) {
    const $tbody = $('#budgetTableBody');
    $tbody.empty();

    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let totalAllocated = 0, totalSpent = 0, totalReserved = 0;

    $.each(departments, function(i, dept) {
        const allocated = parseFloat(dept.allocated);
        const spent = parseFloat(dept.actual_spent);
        const reserved = parseFloat(dept.reserved);
        
        // ERP Logic: Available = Allocated - Spent - Reserved
        const available = allocated - spent - reserved;
        
        // Progress Bar Calculation
        const spentPct = allocated > 0 ? (spent / allocated) * 100 : 0;
        const reservedPct = allocated > 0 ? (reserved / allocated) * 100 : 0;
        const barColor = available < 0 ? 'bg-danger' : 'bg-success';

        totalAllocated += allocated;
        totalSpent += spent;
        totalReserved += reserved;

        $tbody.append(`
            <tr>
                <td class="fw-bold">${dept.department_name}</td>
                <td>${fmt(allocated)}</td>
                <td class="text-warning">${fmt(reserved)}</td>
                <td class="text-danger">${fmt(spent)}</td>
                <td class="fw-bold text-${available < 0 ? 'danger' : 'success'}">${fmt(available)}</td>
                <td style="width: 20%;">
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar ${barColor}" style="width: ${spentPct}%"></div>
                        <div class="progress-bar bg-warning opacity-75" style="width: ${reservedPct}%"></div>
                    </div>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light text-primary" onclick="editBudget(${dept.department_id}, ${allocated})">
                        <i class="bi bi-pencil-square"></i> Update
                    </button>
                </td>
            </tr>
        `);
    });

    // Update Top Summary Cards
    $('#tot-allocated').text(fmt(totalAllocated));
    $('#tot-spent').text(fmt(totalSpent));
    $('#tot-reserved').text(fmt(totalReserved));
    $('#tot-available').text(fmt(totalAllocated - totalSpent - totalReserved));
}

// Helper to open modal for editing
function editBudget(id, amount) {
    $('#dept_id').val(id);
    $('#allocated_amount').val(amount);
    $('#fiscal_year').val(new Date().getFullYear());
    $('#allocateModal').modal('show');
}