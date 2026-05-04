// assets/js/procurement.js

$(document).ready(function() {
    // 1. Load history immediately when the page loads
    loadPOHistory();

    // 2. Fetch pending orders ONLY when the modal opens
    $('#poApprovalModal').on('show.bs.modal', function () {
        loadPendingPOs();
    });
});

function loadPOHistory() {
    const $tbody = $('#poHistoryBody');
    $tbody.html('<tr><td colspan="5" class="text-center py-4">Loading PO history...</td></tr>');

    $.ajax({
        url: '../api/procurement_api.php',
        type: 'GET',
        data: { action: 'get_history' },
        dataType: 'json',
        success: function(response) {
            $tbody.empty();
            if (response.status === 'success' && response.data.length > 0) {
                const fmt = (num) => {
                    let parsed = parseFloat(num);
                    if (isNaN(parsed)) return '₱0.00';
                    return '₱' + parsed.toLocaleString('en-PH', {minimumFractionDigits: 2});
                };
                
                $.each(response.data, function(i, po) {
                    let badgeClass = po.status === 'Received' ? 'bg-success' : 'bg-danger';
                    let icon = po.status === 'Received' ? 'bi-check-circle' : 'bi-x-circle';
                    
                    $tbody.append(`
                        <tr>
                            <td class="fw-bold text-primary">PO-${String(po.po_id).padStart(4, '0')}</td>
                            <td class="fw-bold text-dark">${po.supplier_name}</td>
                            <td class="text-muted small">${po.order_date}</td>
                            <td class="fw-bold text-end">${fmt(po.total_amount)}</td>
                            <td><span class="badge ${badgeClass} text-white"><i class="bi ${icon} me-1"></i> ${po.status}</span></td>
                        </tr>
                    `);
                });
            } else {
                $tbody.append('<tr><td colspan="5" class="text-center py-4 text-muted">No processed purchase orders found.</td></tr>');
            }
        },
        error: function() {
            $tbody.html('<tr><td colspan="5" class="text-center text-danger py-4">Failed to load history.</td></tr>');
        }
    });
}

function loadPendingPOs() {
    const $tbody = $('#pendingPOsBody');
    $tbody.html('<tr><td colspan="6" class="text-center py-4">Fetching pending purchase orders...</td></tr>');

    $.ajax({
        url: '../api/procurement_api.php',
        type: 'GET',
        data: { action: 'get_pending' },
        dataType: 'json',
        success: function(response) {
            $tbody.empty();
            if (response.status === 'success' && response.data.length > 0) {
                const fmt = (num) => {
                    let parsed = parseFloat(num);
                    if (isNaN(parsed)) return '₱0.00';
                    return '₱' + parsed.toLocaleString('en-PH', {minimumFractionDigits: 2});
                };
                
                $.each(response.data, function(i, po) {
                    let badgeClass = po.status === 'Draft' ? 'bg-secondary' : 'bg-info';
                    
                    $tbody.append(`
                        <tr>
                            <td class="fw-bold text-primary">PO-${String(po.po_id).padStart(4, '0')}</td>
                            <td class="fw-bold text-dark">${po.supplier_name}</td>
                            <td class="text-muted small">${po.order_date}</td>
                            <td class="fw-bold text-end">${fmt(po.total_amount)}</td>
                            <td><span class="badge ${badgeClass} text-white"><i class="bi bi-hourglass-split me-1"></i> ${po.status}</span></td>
                            <td class="text-end">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-success fw-bold" onclick="updatePOStatus(${po.po_id}, 'Received')">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger fw-bold" onclick="updatePOStatus(${po.po_id}, 'Cancelled')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `);
                });
            } else {
                $tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">No pending purchase orders require approval.</td></tr>');
            }
        },
        error: function() {
            $tbody.html('<tr><td colspan="6" class="text-center text-danger py-4">Failed to connect to Procurement API.</td></tr>');
        }
    });
}

function updatePOStatus(po_id, new_status) {
    let actionWord = new_status === 'Cancelled' ? 'CANCEL' : 'APPROVE';
    
    if (!confirm(`Are you sure you want to ${actionWord} Purchase Order #${po_id}?`)) {
        return;
    }

    $.ajax({
        url: '../api/procurement_api.php',
        type: 'POST',
        data: { 
            action: 'update_status', 
            po_id: po_id,
            status: new_status
        },
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                alert(response.message);
                loadPendingPOs(); // Refresh the modal table
                loadPOHistory();  // Refresh the main dashboard table immediately
            } else {
                alert("Error: " + response.message);
            }
        },
        error: function() {
            alert("Network error while processing PO update.");
        }
    });
}