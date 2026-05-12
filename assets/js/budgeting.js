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

    // --- NEW: Click Listener for the "View Details" Eye Button ---
    $(document).on('click', '.btn-view-details', function() {
        let deptId = $(this).data('id');
        let deptName = $(this).data('name');
        let year = $('#fiscal_year').val() || currentYear; 

        // Update Modal Headers
        $('#detailDeptName').text(deptName);
        $('#detailYear').text('Expense Breakdown for ' + year);
        $('#detailTableBody').html('<tr><td colspan="4" class="text-center py-4"><span class="spinner-border spinner-border-sm text-success"></span> Loading records...</td></tr>');
        
        // Open the Modal
        $('#detailsModal').modal('show');

        // Fetch the details
        $.ajax({
            url: `../api/get_department_details.php?dept_id=${deptId}&year=${year}`,
            type: 'GET',
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    let rows = '';
                    if (res.data.length === 0) {
                        rows = '<tr><td colspan="4" class="text-center text-muted py-3">No expenses recorded for this department yet.</td></tr>';
                    } else {
                        res.data.forEach(item => {
                            let badge = item.type === 'Payroll' 
                                ? '<span class="badge bg-info bg-opacity-10 text-info border border-info">Payroll</span>' 
                                : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Ledger</span>';

                            rows += `
                                <tr>
                                    <td class="ps-4 text-muted small">${item.date}</td>
                                    <td>${badge}</td>
                                    <td>${item.description}</td>
                                    <td class="text-end pe-4 fw-bold text-danger">₱${parseFloat(item.amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
                                </tr>
                            `;
                        });
                    }
                    $('#detailTableBody').html(rows);
                } else {
                    $('#detailTableBody').html(`<tr><td colspan="4" class="text-danger text-center">${res.message}</td></tr>`);
                }
            },
            error: function() {
                $('#detailTableBody').html(`<tr><td colspan="4" class="text-danger text-center">Failed to fetch data.</td></tr>`);
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
            // Note: Updated to look for response.departments to match the new API
            if (response.success && response.departments) {
                renderBudgetTable(response.departments);
            } else if (response.success && response.data) {
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
        
        // Check for either data structure (old or new API)
        const spent = parseFloat(dept.spent !== undefined ? dept.spent : dept.actual_spent);
        const reserved = parseFloat(dept.reserved);
        
        // Ensure we have an ID for the edit function
        const deptId = dept.id !== undefined ? dept.id : dept.department_id;
        const deptName = dept.name !== undefined ? dept.name : dept.department_name;
        
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
                <td class="fw-bold">${deptName}</td>
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
                    <button class="btn btn-sm btn-light border btn-view-details me-1" data-id="${deptId}" data-name="${deptName}" title="View Details">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-light text-primary border" onclick="editBudget(${deptId}, ${allocated})" title="Update Budget">
                        <i class="bi bi-pencil-square"></i>
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
