// assets/js/ledger.js

$(document).ready(function() {
    // Load the ledger data immediately when the page loads
    loadLedger();

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
