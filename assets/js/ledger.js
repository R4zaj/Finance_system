// assets/js/ledger.js

$(document).ready(function() {
    loadLedgerData();

    // Set today's date safely on load
    const today = new Date().toISOString().split('T')[0];
    $('#trans_date').val(today);

    // Handle Manual Entry Form Submission
    $('#journalEntryForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            trans_date: $('#trans_date').val(),
            amount: parseFloat($('#amount').val()),
            debit_account: $('#debit_account').val(),
            credit_account: $('#credit_account').val(),
            department_id: $('#department_id').val(),
            description: $('#description').val()
        };

        $.ajax({
            url: '../api/process_journal_entry.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    $('#entryModal').modal('hide');
                    $('#journalEntryForm')[0].reset();
                    $('#trans_date').val(today); // Reset date
                    loadLedgerData(); // Refresh table
                } else {
                    alert("Error: " + response.message);
                }
            }
        });
    });
});

function loadLedgerData() {
    $.ajax({
        url: '../api/get_ledger.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderLedgerTable(response.transactions);
                populateDropdowns(response.accounts, response.departments);
            }
        }
    });
}

function renderLedgerTable(transactions) {
    const $tbody = $('#ledgerTableBody');
    $tbody.empty();

    if (transactions.length === 0) {
        $tbody.append('<tr><td colspan="5" class="text-center py-4">No transactions found.</td></tr>');
        return;
    }

    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', { minimumFractionDigits: 2 });

    $.each(transactions, function(i, t) {
        const badge = t.type === 'Credit' ? 'bg-success' : 'bg-danger';
        const dept = t.department_name ? `<br><small class="text-muted"><i class="bi bi-building"></i> ${t.department_name}</small>` : '';
        
        $tbody.append(`
            <tr>
                <td>${t.trans_date}</td>
                <td class="fw-bold">${t.account_name}</td>
                <td><span class="badge ${badge} bg-opacity-10 text-${t.type === 'Credit' ? 'success' : 'danger'} px-2 py-1">${t.type}</span></td>
                <td class="fw-bold text-${t.type === 'Credit' ? 'success' : 'danger'}">${fmt(t.amount)}</td>
                <td>${t.description} ${dept}</td>
            </tr>
        `);
    });
}

function populateDropdowns(accounts, departments) {
    const $drSelect = $('#debit_account');
    const $crSelect = $('#credit_account');
    const $deptSelect = $('#department_id');

    // Only populate if they are currently empty to prevent duplicates on refresh
    if ($drSelect.children().length <= 1) {
        $.each(accounts, function(i, a) {
            const opt = `<option value="${a.account_id}">${a.name} (${a.type})</option>`;
            $drSelect.append(opt);
            $crSelect.append(opt);
        });

        $.each(departments, function(i, d) {
            $deptSelect.append(`<option value="${d.department_id}">${d.name}</option>`);
        });
    }
}