// assets/js/accounts.js

$(document).ready(function() {
    // Automatically fetch accounts when the page loads
    fetchAccounts();
});

function fetchAccounts() {
    const $tableBody = $('#accountsTableBody');
    
    // 1. Show loading state
    $tableBody.html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <span class="spinner-border text-success spinner-border-sm me-2" role="status" aria-hidden="true"></span> 
                Loading accounts...
            </td>
        </tr>
    `);

    // 2. Perform the jQuery AJAX request
    $.ajax({
        url: '../api/get_accounts.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                populateTable(response.data);
            } else {
                $tableBody.html(`<tr><td colspan="6" class="text-center text-danger py-4">${response.message}</td></tr>`);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX Error:", error);
            $tableBody.html('<tr><td colspan="6" class="text-center text-danger py-4">A network error occurred while loading data.</td></tr>');
        }
    });
}
$('#accountForm').on('submit', function(e) {
    e.preventDefault();
    
    const payload = {
        is_edit: $('#is_edit').val() === 'true',
        account_id: $('#account_id').val(),
        name: $('#account_name').val(),
        type: $('#account_type').val()
    };

    $.ajax({
        url: '../api/save_account.php',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(payload),
        success: function(response) {
            if (response.status === 'success') {
                $('#accountModal').modal('hide');
                fetchAccounts(); // Refresh the table
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function() {
            alert("Network error while saving the account.");
        }
    });
});

// Helper function to prepare the modal for a NEW account
function openAddModal() {
    $('#accountForm')[0].reset();
    $('#is_edit').val('false');
    $('#account_id').prop('readonly', false); // Allow typing a new ID
    $('#modalTitle').text('Create New Account');
    $('#accountModal').modal('show');
}

// Replace your existing edit button click handler inside populateTable() with this:
$('.edit-btn').on('click', function() {
    const accountId = $(this).data('id');
    const accountName = $(this).closest('tr').find('td:nth-child(2) .fw-bold').text();
    const accountType = $(this).closest('tr').find('td:nth-child(3) .badge').text();

    // Populate the modal
    $('#is_edit').val('true');
    $('#account_id').val(accountId).prop('readonly', true); // Prevent changing ID on edit
    $('#account_name').val(accountName);
    $('#account_type').val(accountType);
    
    $('#modalTitle').text('Edit Account');
    $('#accountModal').modal('show');
});

function populateTable(accounts) {
    const $tableBody = $('#accountsTableBody');
    $tableBody.empty(); // Clear the loading state

    // Handle empty database
    if (accounts.length === 0) {
        $tableBody.html('<tr><td colspan="6" class="text-center text-muted py-4">No accounts found in the system.</td></tr>');
        return;
    }

    // Iterate through the JSON array using jQuery's $.each
    $.each(accounts, function(index, account) {
        
        // Determine badge class dynamically based on account type
        let badgeClass = '';
        switch(account.type) {
            case 'Asset': badgeClass = 'badge-asset'; break;
            case 'Liability': badgeClass = 'badge-liability'; break;
            case 'Revenue': badgeClass = 'badge-revenue'; break;
            case 'Expense': badgeClass = 'badge-expense'; break;
            default: badgeClass = 'bg-secondary text-white';
        }

        // Format Account ID with leading zeros (e.g., "001")
        let formattedId = String(account.account_id).padStart(3, '0');

        // Build the HTML row
        let rowHtml = `
            <tr>
                <td><span class="fw-bold text-muted">${formattedId}</span></td>
                <td><span class="fw-bold text-dark">${account.name}</span></td>
                <td><span class="badge ${badgeClass} px-2 py-1">${account.type}</span></td>
                <td><span class="text-muted small fst-italic">Balance calculated via Ledger</span></td>
                <td><i class="bi bi-circle-fill text-success" style="font-size: 0.6rem;"></i> <small class="text-muted ms-1">Active</small></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-light text-primary edit-btn" data-id="${account.account_id}">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
            </tr>
        `;
        
        // Append row to table
        $tableBody.append(rowHtml);
    });

    // Attach click event for dynamically created edit buttons
    $('.edit-btn').on('click', function() {
        let accountId = $(this).data('id');
        alert("Edit functionality for Account ID: " + accountId + " will be implemented next.");
    });
}