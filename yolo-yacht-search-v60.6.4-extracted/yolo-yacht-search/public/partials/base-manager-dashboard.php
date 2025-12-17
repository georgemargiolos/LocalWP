<?php
/**
 * Base Manager Dashboard Template
 *
 * @package YOLO_Yacht_Search
 * @subpackage Base_Manager
 * @since 17.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
?>

<div class="yolo-base-manager-dashboard">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="dashboard-title">
                    <i class="fas fa-anchor"></i> Base Manager Dashboard
                </h1>
                <p class="text-muted">Welcome, <?php echo esc_html($user->display_name); ?></p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="baseManagerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings" type="button" role="tab">
                    <i class="fas fa-calendar-alt"></i> Bookings Calendar
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="yachts-tab" data-bs-toggle="tab" data-bs-target="#yachts" type="button" role="tab">
                    <i class="fas fa-ship"></i> Yacht Management
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="checkin-tab" data-bs-toggle="tab" data-bs-target="#checkin" type="button" role="tab">
                    <i class="fas fa-clipboard-check"></i> Check-In
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="checkout-tab" data-bs-toggle="tab" data-bs-target="#checkout" type="button" role="tab">
                    <i class="fas fa-clipboard-list"></i> Check-Out
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="warehouse-tab" data-bs-toggle="tab" data-bs-target="#warehouse" type="button" role="tab">
                    <i class="fas fa-warehouse"></i> Warehouse
                </button>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="baseManagerTabContent">
            
            <!-- Bookings Calendar Tab -->
            <div class="tab-pane fade show active" id="bookings" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Bookings Calendar</h3>
                    </div>
                    <div class="card-body">
                        <div id="bookings-calendar-container">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover" id="bookings-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Yacht</th>
                                            <th>Check-In</th>
                                            <th>Check-Out</th>
                                            <th>Status</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bookings-tbody">
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yacht Management Tab -->
            <div class="tab-pane fade" id="yachts" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3><i class="fas fa-ship"></i> Yacht Management</h3>
                        <button class="btn btn-primary" id="add-yacht-btn">
                            <i class="fas fa-plus"></i> Add Yacht
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="yachts-list" class="row">
                            <div class="col-12 text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-In Tab -->
            <div class="tab-pane fade" id="checkin" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3><i class="fas fa-clipboard-check"></i> Check-In Management</h3>
                        <button class="btn btn-success" id="new-checkin-btn">
                            <i class="fas fa-plus"></i> New Check-In
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="checkin-form-container" style="display: none;">
                            <form id="checkin-form">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Booking</label>
                                        <select class="form-select" id="checkin-booking-select" required>
                                            <option value="">Choose booking...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Select Yacht</label>
                                        <select class="form-select" id="checkin-yacht-select" required>
                                            <option value="">Choose yacht...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="checkin-checklist-container"></div>
                                
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Base Manager Signature</label>
                                        <div class="signature-pad-container">
                                            <canvas id="checkin-signature-pad" class="signature-canvas"></canvas>
                                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="clear-checkin-signature">
                                                <i class="fas fa-eraser"></i> Clear Signature
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success" id="complete-checkin-btn">
                                        <i class="fas fa-check"></i> Complete Check-In
                                    </button>
                                    <button type="button" class="btn btn-primary" id="save-checkin-pdf-btn">
                                        <i class="fas fa-file-pdf"></i> Save PDF
                                    </button>
                                    <button type="button" class="btn btn-info" id="send-checkin-guest-btn">
                                        <i class="fas fa-paper-plane"></i> Send to Guest
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancel-checkin-btn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div id="checkin-list">
                            <h4 class="mt-4">Previous Check-Ins</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Booking</th>
                                            <th>Yacht</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="checkin-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No check-ins yet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Check-Out Tab -->
            <div class="tab-pane fade" id="checkout" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3><i class="fas fa-clipboard-list"></i> Check-Out Management</h3>
                        <button class="btn btn-warning" id="new-checkout-btn">
                            <i class="fas fa-plus"></i> New Check-Out
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="checkout-form-container" style="display: none;">
                            <form id="checkout-form">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Select Booking</label>
                                        <select class="form-select" id="checkout-booking-select" required>
                                            <option value="">Choose booking...</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Select Yacht</label>
                                        <select class="form-select" id="checkout-yacht-select" required>
                                            <option value="">Choose yacht...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div id="checkout-checklist-container"></div>
                                
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <label class="form-label">Base Manager Signature</label>
                                        <div class="signature-pad-container">
                                            <canvas id="checkout-signature-pad" class="signature-canvas"></canvas>
                                            <button type="button" class="btn btn-sm btn-secondary mt-2" id="clear-checkout-signature">
                                                <i class="fas fa-eraser"></i> Clear Signature
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning" id="complete-checkout-btn">
                                        <i class="fas fa-check"></i> Complete Check-Out
                                    </button>
                                    <button type="button" class="btn btn-primary" id="save-checkout-pdf-btn">
                                        <i class="fas fa-file-pdf"></i> Save PDF
                                    </button>
                                    <button type="button" class="btn btn-info" id="send-checkout-guest-btn">
                                        <i class="fas fa-paper-plane"></i> Send to Guest
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="cancel-checkout-btn">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                        
                        <div id="checkout-list">
                            <h4 class="mt-4">Previous Check-Outs</h4>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Booking</th>
                                            <th>Yacht</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="checkout-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No check-outs yet</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warehouse Tab -->
            <div class="tab-pane fade" id="warehouse" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-warehouse"></i> Warehouse Management</h3>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Select Yacht</label>
                                <select class="form-select" id="warehouse-yacht-select">
                                    <option value="">Choose yacht...</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button class="btn btn-primary" id="add-warehouse-item-btn">
                                    <i class="fas fa-plus"></i> Add Item
                                </button>
                            </div>
                        </div>
                        
                        <div id="warehouse-items-container">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Quantity</th>
                                            <th>Expiry Date</th>
                                            <th>Location</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="warehouse-tbody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Select a yacht to view inventory</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Yacht Modal -->
<div class="modal fade" id="yachtModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Yacht</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="yacht-form" enctype="multipart/form-data">
                    <input type="hidden" id="yacht-id" name="yacht_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Yacht Name *</label>
                            <input type="text" class="form-control" id="yacht-name" name="yacht_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Yacht Model *</label>
                            <input type="text" class="form-control" id="yacht-model" name="yacht_model" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Logo *</label>
                            <input type="file" class="form-control" id="company-logo" name="company_logo" accept="image/*">
                            <div id="company-logo-preview" class="mt-2"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Boat Logo (Optional)</label>
                            <input type="file" class="form-control" id="boat-logo" name="boat_logo" accept="image/*">
                            <div id="boat-logo-preview" class="mt-2"></div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Boat Owner Information</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Owner Name *</label>
                            <input type="text" class="form-control" id="owner-name" name="owner_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Owner Surname *</label>
                            <input type="text" class="form-control" id="owner-surname" name="owner_surname" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Owner Mobile *</label>
                            <input type="tel" class="form-control" id="owner-mobile" name="owner_mobile" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Owner Email *</label>
                            <input type="email" class="form-control" id="owner-email" name="owner_email" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-yacht-btn">Save Yacht</button>
            </div>
        </div>
    </div>
</div>

<!-- Equipment Category Modal -->
<div class="modal fade" id="equipmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Equipment Categories</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Category Name</label>
                    <input type="text" class="form-control" id="category-name" placeholder="e.g., Electronics, Kitchenware">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Items</label>
                    <div id="equipment-items-container"></div>
                    <button type="button" class="btn btn-sm btn-success mt-2" id="add-equipment-item-btn">
                        <i class="fas fa-plus"></i> Add Item
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-equipment-category-btn">Save Category</button>
            </div>
        </div>
    </div>
</div>

<!-- Warehouse Item Modal -->
<div class="modal fade" id="warehouseItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add/Edit Warehouse Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="warehouse-item-form">
                    <input type="hidden" id="warehouse-item-id">
                    <input type="hidden" id="warehouse-item-yacht-id">
                    
                    <div class="mb-3">
                        <label class="form-label">Item Name *</label>
                        <input type="text" class="form-control" id="warehouse-item-name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity *</label>
                        <div class="input-group">
                            <button type="button" class="btn btn-outline-secondary" id="decrease-quantity">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="warehouse-item-quantity" value="0" min="0" required>
                            <button type="button" class="btn btn-outline-secondary" id="increase-quantity">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" class="form-control" id="warehouse-item-expiry">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <input type="text" class="form-control" id="warehouse-item-location" placeholder="e.g., Shelf A3">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-warehouse-item-btn">Save Item</button>
            </div>
        </div>
    </div>
</div>
