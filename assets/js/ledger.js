// assets/js/ledger.js

$(document).ready(function() {
    // Load the ledger data immediately when the page loads
    loadLedger();
    loadAccounts();
    // Fix for the aria-hidden console warning
    $('#newEntryModal').on('hide.bs.modal', function () {
        // Find whatever has focus inside the modal (like the Close or Save button) and blur (unfocus) it
        $(':focus', this).blur();
    });
    // -----------------------------------------
    // 1. EXPORT TO CSV FUNCTIONALITY
    // -----------------------------------------
    $('#btnExport').on('click', function() {
        let csv = 'Date,Account,Type,Description,Debit,Credit\n'; // CSV Headers
        
        // Loop through only the valid data rows in the table
        $('#ledgerTableBody tr').each(function() {
            let cols = $(this).find('td');
            // Check if it's a real data row (6 columns) and not an error/loading message
            if (cols.length === 6) {
                let row = [];
                cols.each(function() {
                    // Clean the text, remove the peso sign and commas, and wrap in quotes for CSV safety
                    let text = $(this).text().trim().replace(/₱/g, '').replace(/,/g, '');
                    row.push('"' + text + '"'); 
                });
                csv += row.join(',') + '\n';
            }
        });

        // Trigger the file download
        let blob = new Blob([csv], { type: 'text/csv' });
        let link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        link.download = 'Ledger_Export_' + new Date().toISOString().split('T')[0] + '.csv';
        link.click();
    });

    // -----------------------------------------
    // 2. SUBMIT NEW ENTRY FORM
    // -----------------------------------------
    $('#journalEntryForm').on('submit', function(e) {
        e.preventDefault(); // Stop the page from reloading
        let $btn = $('#btnSaveEntry');
        let originalText = $btn.html();
        
        // Show saving state
        $btn.html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');
        $btn.prop('disabled', true);

        // Send data to your API
        $.ajax({
            url: '../api/process_journal_entry.php', // Your API file
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#newEntryModal').modal('hide'); // Close modal
                    $('#journalEntryForm')[0].reset(); // Clear form
                    loadLedger(); // Refresh the table automatically!
                    
                    // Optional: Show a little success alert (if you want)
                    alert('Entry saved successfully!'); 
                } else {
                    alert('Error saving entry: ' + response.message);
                }
            },
            error: function() {
                alert('Server error while saving the entry.');
            },
            complete: function() {
                // Restore button state
                $btn.html(originalText);
                $btn.prop('disabled', false);
            }
        });
    });

    // Make the search bar functional
    $('input[placeholder="Search entries..."]').on('keyup', function() {
        let value = $(this).val().toLowerCase();
        $("#ledgerTableBody tr").filter(function() {
            // Don't filter out the error/empty messages
            if ($(this).find('td').length === 1) return;
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function loadLedger() {
    const $tbody = $('#ledgerTableBody');
    
    // Show loading state (just in case it's not already there)
    $tbody.html(`
        <tr>
            <td colspan="6" class="text-center py-4 text-muted">
                <div class="spinner-border text-success spinner-border-sm me-2" role="status"></div>
                Initializing ledger...
            </td>
        </tr>
    `);

    $.ajax({
        url: '../api/get_ledger.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $tbody.empty();

            if (response.status === 'success') {
                if (response.data.length > 0) {
                    
                    // Helper to format currency
                    const fmtCurrency = (num) => {
                        let parsed = parseFloat(num);
                        if (isNaN(parsed)) return '-';
                        return new Intl.NumberFormat('en-PH', { 
                            style: 'currency', 
                            currency: 'PHP' 
                        }).format(parsed);
                    };

                    // Helper to format date cleanly (e.g., Oct 24, 2026)
                    const fmtDate = (dateStr) => {
                        if (!dateStr) return '-';
                        let d = new Date(dateStr);
                        return d.toLocaleDateString('en-PH', { year: 'numeric', month: 'short', day: 'numeric' });
                    };

                    // Helper to map database account types to your custom CSS classes
                    const getBadgeClass = (type) => {
                        const t = (type || '').toLowerCase();
                        if (t.includes('asset')) return 'badge-asset';
                        if (t.includes('liabilit')) return 'badge-liability';
                        if (t.includes('equity')) return 'badge-equity';
                        if (t.includes('revenue') || t.includes('income')) return 'badge-revenue';
                        if (t.includes('expense')) return 'badge-expense';
                        return 'bg-secondary text-white'; // Fallback
                    };

                    // Loop through the data and build the rows
                    $.each(response.data, function(i, entry) {
                        // Figure out if the amount goes in the Debit or Credit column
                        let isDebit = (entry.trans_type === 'Debit');
                        let debitText = isDebit ? fmtCurrency(entry.amount) : '';
                        let creditText = !isDebit ? fmtCurrency(entry.amount) : '';
                        
                        let badgeClass = getBadgeClass(entry.account_type);

                        $tbody.append(`
                            <tr>
                                <td class="text-muted small">${fmtDate(entry.trans_date)}</td>
                                <td class="fw-bold text-dark">${entry.account_name}</td>
                                <td><span class="badge ${badgeClass} rounded-pill px-3">${entry.account_type}</span></td>
                                <td class="text-muted text-truncate" style="max-width: 250px;">${entry.description || '-'}</td>
                                <td class="text-end fw-semibold text-dark">${debitText}</td>
                                <td class="text-end fw-semibold text-dark">${creditText}</td>
                            </tr>
                        `);
                    });
                } else {
                    $tbody.append(`
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-2 d-block mb-2 opacity-50"></i>
                                No ledger entries found. Click "New Entry" to start.
                            </td>
                        </tr>
                    `);
                }
            } else {
                // 🚨 THIS IS THE MAGIC ERROR CATCHER 🚨
                // If PHP sends an error message (like a missing table), it prints right here!
                $tbody.append(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger fw-bold bg-danger bg-opacity-10">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> ${response.message}
                        </td>
                    </tr>
                `);
            }
        },
        error: function(xhr, status, error) {
            // This catches massive server crashes (like 500 errors) or dropped internet
            console.error("AJAX Error:", xhr.responseText);
            $tbody.html(`
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger fw-bold bg-danger bg-opacity-10">
                        <i class="bi bi-wifi-off me-2"></i> Failed to connect to the database. Check console for details.
                    </td>
                </tr>
            `);
        }
    });
}
// -----------------------------------------
// 3. LOAD ACCOUNTS FOR THE DROPDOWN
// -----------------------------------------
function loadAccounts() {
    $.ajax({
        url: '../api/get_accounts.php', // Looks for this file in your API folder
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            let $select = $('#accountSelect');
            
            // Clear out any old options, but keep the first "Select an account..." placeholder
            $select.find('option:not(:first)').remove();

            // Check if the API sent back a success message
            if (response.status === 'success' || response.success) {
                // Loop through the database accounts and add them to the dropdown
                $.each(response.data, function(i, account) {
                    // This formats it nicely, e.g., "Cash (Asset)" or "Tuition Revenue (Revenue)"
                    $select.append(`<option value="${account.account_id}">${account.name} (${account.type})</option>`);
                });
            } else {
                $select.append(`<option disabled class="text-danger">Error loading accounts</option>`);
            }
        },
        error: function(xhr) {
            console.error("Account Load Error:", xhr.responseText);
            $('#accountSelect').append(`<option disabled class="text-danger">Failed to connect to database</option>`);
        }
    });
}
