// assets/js/ap_ar.js

$(document).ready(function() {
    loadAPARData();

    // Handle AP Payment Submission
    $('#apPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const payload = {
            po_id: $('#pay_po_id').val(),
            supplier_id: $('#pay_supplier_id').val(),
            amount: parseFloat($('#pay_amount').val()),
            pay_date: $('#pay_date').val()
        };

        $.ajax({
            url: '../api/process_ap_payment.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(response) {
                if (response.success) {
                    $('#apPaymentModal').modal('hide');
                    $('#apPaymentForm')[0].reset();
                    document.getElementById('pay_date').valueAsDate = new Date();
                    loadAPARData(); // Refresh all tables and stats
                } else {
                    alert("Error: " + response.message);
                }
            }
        });
    });
});

function loadAPARData() {
    $.ajax({
        url: '../api/get_ap_ar_data.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderAPTable(response.outstanding_ap);
                renderAPHistory(response.ap_history);
                
                // Format and update AR Stat
                const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2});
                $('#stat-ar-collected').text(fmt(response.total_ar));
            }
        }
    });
}

function renderAPTable(outstanding) {
    const $tbody = $('#apOutstandingBody');
    $tbody.empty();
    let totalOutstanding = 0;
    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2});

    if (outstanding.length === 0) {
        $tbody.append('<tr><td colspan="6" class="text-center py-4">No outstanding payables found.</td></tr>');
        $('#stat-ap-due').text('₱0.00');
        return;
    }

    $.each(outstanding, function(i, item) {
        const total = parseFloat(item.total_amount);
        const paid = parseFloat(item.paid_amount);
        const balance = total - paid;
        totalOutstanding += balance;

        $tbody.append(`
            <tr>
                <td>PO-${item.po_id}</td>
                <td class="fw-bold">${item.supplier_name}</td>
                <td>${item.order_date}</td>
                <td>${fmt(total)}</td>
                <td class="text-danger fw-bold">${fmt(balance)}</td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-success" onclick="openPaymentModal(${item.po_id}, ${item.supplier_id}, '${item.supplier_name}', ${balance})">
                        Pay Vendor
                    </button>
                </td>
            </tr>
        `);
    });

    $('#stat-ap-due').text(fmt(totalOutstanding));
}

function renderAPHistory(history) {
    const $tbody = $('#apHistoryBody');
    $tbody.empty();
    const fmt = (num) => '₱' + parseFloat(num).toLocaleString('en-PH', {minimumFractionDigits: 2});

    if (history.length === 0) {
        $tbody.append('<tr><td colspan="4" class="text-center py-4 text-muted">No recent payments.</td></tr>');
        return;
    }

    $.each(history, function(i, h) {
        $tbody.append(`
            <tr>
                <td>${h.pay_date}</td>
                <td>PO-${h.po_id}</td>
                <td class="fw-bold">${h.supplier_name}</td>
                <td class="text-success fw-bold text-end">${fmt(h.amount)}</td>
            </tr>
        `);
    });
}

function openPaymentModal(po_id, supplier_id, supplier_name, balance) {
    $('#pay_po_id').val(po_id);
    $('#pay_supplier_id').val(supplier_id);
    $('#display_supplier').val(supplier_name + ' (PO-' + po_id + ')');
    $('#pay_amount').val(balance).attr('max', balance); // Set default to max balance
    $('#apPaymentModal').modal('show');
}