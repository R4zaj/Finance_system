$(document).ready(function() {
    
    // 1. Fetch POs from the external API when the modal opens
    $('#poApprovalModal').on('show.bs.modal', function () {
        loadPendingPOs();
    });

    function loadPendingPOs() {
        $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4"><span class="spinner-border spinner-border-sm me-2"></span>Connecting to Inventory System...</td></tr>');
        
        $.ajax({
            url: '../api/procurement_api.php?action=get_pos',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                // NOTE: Depending on what icis-inventory sends back, 'response.data' might need to be adjusted.
                let html = '';
                
                // Assuming the external API sends { success: true, data: [...] }
                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(i, po) {
                        // Only show pending/draft POs that need approval
                        if(po.status === 'Draft' || po.status === 'Pending') {
                            html += `
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">#PO-${po.po_id || po.id}</td>
                                    <td>${po.supplier_name || 'Unknown Supplier'}</td>
                                    <td class="text-muted small">${po.order_date || po.date}</td>
                                    <td class="text-end fw-semibold text-danger">₱${parseFloat(po.total_amount || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
                                    <td class="text-end pe-4">
                                        <div class="btn-group">
                                            <button class="btn btn-sm btn-success btn-update-po" data-id="${po.po_id || po.id}" data-status="Ordered">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-update-po" data-id="${po.po_id || po.id}" data-status="Cancelled">
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
            error: function() {
                $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error connecting to the external Inventory API.</td></tr>');
            }
        });
    }

    // 2. Handle the Approve or Cancel click
    $(document).on('click', '.btn-update-po', function() {
        let poId = $(this).data('id');
        let newStatus = $(this).data('status');
        let $row = $(this).closest('tr');
        
        // Show loading state on the clicked row
        $(this).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $(this).siblings('button').prop('disabled', true);

        $.ajax({
            url: '../api/procurement_api.php?action=update_status',
            type: 'POST',
            data: JSON.stringify({ po_id: poId, new_status: newStatus }),
            contentType: 'application/json',
            success: function(response) {
                if (response.success) {
                    // Visually remove the row from the approval queue with a smooth fade
                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#poTableBody tr').length === 0) {
                            $('#poTableBody').html('<tr><td colspan="5" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>');
                        }
                    });
                } else {
                    alert('Error: ' + response.message);
                    loadPendingPOs(); // Reload table on error to restore buttons
                }
            }
        });
    });
});
