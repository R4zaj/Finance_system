$(document).ready(function() {
    
    window.recentlyProcessed = window.recentlyProcessed || [];

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
                    
                    let processedPOs = response.data.filter(po => po.status !== 'Pending' && po.status !== 'Draft');

                    processedPOs.sort(function(a, b) {
                        let aIsRecent = window.recentlyProcessed.includes(a.po_id) ? 1 : 0;
                        let bIsRecent = window.recentlyProcessed.includes(b.po_id) ? 1 : 0;
                        
                        if (aIsRecent !== bIsRecent) {
                            return bIsRecent - aIsRecent;
                        }

                        let dateA = new Date(a.updated_at || a.last_updated || a.order_date).getTime() || 0;
                        let dateB = new Date(b.updated_at || b.last_updated || b.order_date).getTime() || 0;
                        
                        if (dateA !== dateB) {
                            return dateB - dateA;
                        } else {
                            return parseInt(b.po_id) - parseInt(a.po_id);
                        }
                    });

                    $.each(processedPOs, function(i, po) {
                        let badgeColor = 'bg-secondary';
                        if(po.status === 'Approved' || po.status === 'Received') badgeColor = 'bg-success';
                        if(po.status === 'Cancelled') badgeColor = 'bg-danger';

                        let isHighlighted = window.recentlyProcessed.includes(po.po_id) ? 'bg-success bg-opacity-10' : '';

                        html += `
                            <tr class="${isHighlighted}">
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

    $('#poApprovalModal').on('hide.bs.modal', function () {
        if (document.activeElement && $.contains(this, document.activeElement)) {
            document.activeElement.blur();
        }
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
                        if(po.status === 'Pending') {
                            
                            let suppId = po.supplier_id ? po.supplier_id : 999;
                            
                            // NEW: Grab the department ID from the Inventory data (fallback to 1 if missing)
                            let deptId = po.department_id ? po.department_id : 1;

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
                                            <button class="btn btn-sm btn-success btn-update-po" 
                                                data-id="${po.po_id}" 
                                                data-status="Approved"
                                                data-amount="${po.total_amount}"
                                                data-suppname="${po.supplier_name}"
                                                data-suppid="${suppId}"
                                                data-deptid="${deptId}">
                                                <i class="bi bi-check-lg"></i> Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger btn-update-po" 
                                                data-id="${po.po_id}" 
                                                data-status="Cancelled"
                                                data-amount="0"
                                                data-suppname="${po.supplier_name}"
                                                data-suppid="${suppId}"
                                                data-deptid="${deptId}">
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
        
        let poAmount = $(this).data('amount');
        let suppName = $(this).data('suppname');
        let suppId = $(this).data('suppid');
        
        // NEW: Grab the Dept ID from the button
        let deptId = $(this).data('deptid');

        let $row = $(this).closest('tr');
        
        $(this).html('<span class="spinner-border spinner-border-sm"></span>').prop('disabled', true);
        $(this).siblings('button').prop('disabled', true);

        $.ajax({
            url: '../api/procurement_api.php?action=update_status',
            type: 'POST',
            data: JSON.stringify({ 
                po_id: poId, 
                new_status: newStatus,
                amount: parseFloat(poAmount) || 0,
                supplier_name: suppName,
                supplier_id: suppId,
                department_id: deptId // NEW: Send it to the PHP backend!
            }),
            contentType: 'application/json',
            success: function(response) {
                if (response.status === 'success' || response.success) {
                    
                    window.recentlyProcessed.push(poId);

                    $row.fadeOut(300, function() {
                        $(this).remove();
                        if ($('#pendingPOsBody tr').length === 0) {
                            $('#pendingPOsBody').html('<tr><td colspan="6" class="text-center py-4 text-muted">No pending Purchase Orders require approval.</td></tr>');
                        }
                    });

                    setTimeout(function() {
                        loadPOHistory();
                    }, 1500);

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
