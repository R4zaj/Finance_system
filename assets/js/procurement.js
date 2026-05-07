$(document).ready(function() {
    
    // =================================================================
    // 1. MAIN PAGE: LOAD PROCESSED PO HISTORY ON LOAD
    // =================================================================
    loadPOHistory();

    function loadPOHistory() {
        $('#poHistoryBody').html('<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Syncing history with Inventory...</td></tr>');
        
        $.ajax({
            url: '../api/procurement_api.php?action=get_pos',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let html = '';
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $.each(response.data, function(i, po) {
                        // Filter: Only show POs that are ALREADY processed (Approved, Received, Cancelled)
                        if(po.status !== 'Pending' && po.status !== 'Draft') {
                            
                            // Determine badge color based on status
                            let badgeColor = 'bg-secondary';
                            if(po.status === 'Approved' || po.status === 'Received') badgeColor = 'bg-success';
                            if(po.status === 'Cancelled') badgeColor = 'bg-danger';

                            html += `
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">#PO-${po.po_id}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">${po.supplier_name}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-box-seam me-1"></i>${po.item_qty}x ${po.item_name}
                                        </div>
                                    </td>
                                    <td class="text-muted small">${po.order_date}</td>
                                    <td class="text-end fw-semibold">₱${parseFloat(po.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    <td><span class="badge ${badgeColor}">${po.status}</span></td>
                                </tr>
                            `;
                        }
                    });
                    if (html === '') html = '<tr><td colspan="5" class="text-center py-4 text-muted">No processed Purchase Orders found.</td></tr>';
                } else {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">No records found in the Inventory System.</td></tr>';
                }
                $('#poHistoryBody').html(html);
            },
            error: function() {
                $('#poHistoryBody').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error connecting to Inventory API.</td></tr>');
            }
        });
    }

    // =================================================================
    // 2. MODAL: FETCH PENDING POs WHEN MODAL OPENS
    // =================================================================
    $('#poApprovalModal').on('show.bs.modal', function () {
        loadPendingPOs();
    });

    function loadPendingPOs() {
        $('#pendingPOsBody').html('<tr><td colspan="6" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Syncing pending items...</td></tr>');
        
        $.ajax({
            url: '../api/procurement_api.php?action=get_pos',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let html = '';
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $.each(response.data, function(i, po) {
                        // Filter: ONLY show 'Pending' items
                        if(po.status === 'Pending') {
                            html += `
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#PO-${po.po_id}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">${po.supplier_name}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-box-seam me-1"></i>
                                            ${po.item_qty}x ${po.item_name} <br>
                                            <span class="opacity-75">(@ ₱${parseFloat(po.unit_price).toLocaleString(undefined, {minimumFractionDigits: 2})})</span>
                                        </div>
                                    </td>
                                    <td class="text-muted small">${po.order_date}</td>
                                    <td class="text-end fw-semibold text-danger">₱${parseFloat(po.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-success btn-update-po" data-id="${po.po_id}" data-status="Approved">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-update-po" data-id="${po.po_id}" data-status="Cancelled">
                                                <i class="bi bi-x-lg"></i> Cancel
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        }
                    });
                    if (html === '') html = '<tr><td colspan="6" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>';
                } else {
                    html = '<tr><td colspan="6" class="text-center py-4 text-muted">No records found.</td></tr>';
                }
                $('#pendingPOsBody').html(html);
            },
            error: function() {
                $('#pendingPOsBody').html('<tr><td colspan="6" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error connecting.</td></tr>');
            }
        });
    }

    // =================================================================
    // 3. SEND APPROVAL / CANCELLATION ACTION
    // =================================================================
    $(document).on('click', '.btn-update-po', function() {
        let poId = $(this).data('id');
        let newStatus = $(this).data('status');
        let $row = $(this).closest('tr');
        
        // Disable buttons and show spinner
        $(this).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $(this).siblings('button').prop('disabled', true);

        $.ajax({
            url: '../api/procurement_api.php?action=update_status',
            type: 'POST',
            data: JSON.stringify({ po_id: poId, new_status: newStatus }),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success' || response.success) {
                    
                    // Fade out and remove the row from the Modal
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#pendingPOsBody tr').length === 0) {
                            $('#pendingPOsBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>');
                        }
                    });

                    // IMPORTANT: Refresh the background history table to show the new status!
                    loadPOHistory();

                } else {
                    alert('Inventory System Error: ' + (response.message || 'Update failed'));
                    loadPendingPOs(); 
                }
            },
            error: function() {
                alert('Server error while talking to Inventory API.');
                loadPendingPOs();
            }
        });
    });
});
