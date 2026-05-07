<!-- includes/modals.php -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to log out of the Finance System? Any unsaved changes may be lost.
            </div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Yes, Log Out</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="poApprovalModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow card-custom">
            <div class="modal-header bg-dark text-white border-0">
                <div>
                    <h5 class="fw-bold mb-0"><i class="bi bi-cart-check me-2"></i>Finance PO Approval</h5>
                    <small class="text-white-50">Review and authorize pending Purchase Orders from the Inventory System.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light mt-3 p-4">
                <div class="card card-custom border-0 shadow-sm p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 bg-white">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">PO Number</th>
                                    <th>Supplier</th>
                                    <th>Date Requested</th>
                                    <th class="text-end">Total Amount (₱)</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody id="poTableBody">
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
