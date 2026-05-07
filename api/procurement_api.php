<script>
$(document).ready(function() {
    
    // 1. Fetch POs from the external API
    $('#poApprovalModal').on('show.bs.modal', function () {
        loadPendingPOs();
    });

    function loadPendingPOs() {
        $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Syncing with Inventory System...</td></tr>');
        
        $.ajax({
            url: '../api/procurement_api.php?action=get_pos',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                let html = '';
                
                // FIX: Inventory API uses "status": "success", not "success": true
                if (response.status === 'success' && response.data && response.data.length > 0) {
                    $.each(response.data, function(i, po) {
                        // FIX: Specifically look for the "Pending" status from your JSON
                        if(po.status === 'Pending') {
                            html += `
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#PO-${po.po_id}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">${po.supplier_name}</div>
                                        <div class="small text-muted">
                                            <i class="bi bi-box-seam me-1"></i>
                                            ${po.item_qty}x ${po.item_name} (@ ₱${po.unit_price})
                                        </div>
                                    </td>
                                    <td class="text-muted small">${po.order_date}</td>
                                    <td class="text-end fw-semibold text-danger">₱${parseFloat(po.total_amount).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
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
                    
                    if (html === '') html = '<tr><td colspan="5" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>';
                    
                } else {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">No records found in the Inventory System.</td></tr>';
                }
                
                $('#poTableBody').html(html);
            },
            error: function(xhr) {
                console.error(xhr.responseText); // This will log any deep errors to your console!
                $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error connecting to the Inventory API.</td></tr>');
            }
        });
    }

    // 2. Send the Approval / Cancellation back to the API
    $(document).on('click', '.btn-update-po', function() {
        let poId = $(this).data('id');
        let newStatus = $(this).data('status');
        let $row = $(this).closest('tr');
        
        $(this).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $(this).siblings('button').prop('disabled', true);

        $.ajax({
            url: '../api/procurement_api.php?action=update_status',
            type: 'POST',
            data: JSON.stringify({ po_id: poId, new_status: newStatus }),
            contentType: 'application/json',
            success: function(response) {
                // Check against the external API's response format
                if (response.status === 'success' || response.success) {
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#poTableBody tr').length === 0) {
                            $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>');
                        }
                    });
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
</script>
