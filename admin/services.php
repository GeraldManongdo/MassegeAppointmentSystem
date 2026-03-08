<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Manage Services";

$database = new Database();
$db = $database->getConnection();
$service_model = new Service($db);

$success = '';
$error = '';

// Handle create/update
if (isset($_POST['save_service'])) {
    $service_name = trim($_POST['service_name']);
    $description = trim($_POST['description']);
    $duration = (int)$_POST['duration'];
    $price = (float)$_POST['price'];
    $image_url = trim($_POST['image_url']);
    $status = $_POST['status'];
    $service_id = isset($_POST['service_id']) ? (int)$_POST['service_id'] : 0;
    
    if (empty($service_name) || empty($duration) || empty($price)) {
        $error = "Please fill in all required fields.";
    } else {
        if ($service_id > 0) {
            // Update
            $service_model->service_id = $service_id;
            $service_model->service_name = $service_name;
            $service_model->description = $description;
            $service_model->duration = $duration;
            $service_model->price = $price;
            $service_model->image_url = $image_url;
            $service_model->status = $status;
            
            if ($service_model->update()) {
                $success = "Service updated successfully!";
            } else {
                $error = "Failed to update service.";
            }
        } else {
            // Create
            $service_model->service_name = $service_name;
            $service_model->description = $description;
            $service_model->duration = $duration;
            $service_model->price = $price;
            $service_model->image_url = $image_url;
            $service_model->status = $status;
            
            if ($service_model->create()) {
                $success = "Service created successfully!";
            } else {
                $error = "Failed to create service.";
            }
        }
    }
}

// Handle delete
if (isset($_POST['delete_service'])) {
    $service_id = (int)$_POST['service_id'];
    
    if ($service_model->delete($service_id)) {
        $success = "Service deleted successfully!";
    } else {
        $error = "Failed to delete service. It may have existing appointments.";
    }
}

// Get all services
$services = $service_model->getAll();

include '../includes/header.php';
?>

<div class="admin-layout d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <div class="admin-content flex-grow-1">
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="bi bi-grid"></i> Manage Services</h2>
                    <p class="text-muted">Add, edit, and manage your services</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#serviceModal">
                        <i class="bi bi-plus-circle"></i> Add New Service
                    </button>
                </div>
            </div>
    
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle"></i> <?php echo escape($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
    
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
    
            <!-- Services Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Services List 
                        <span class="badge bg-primary"><?php echo count($services); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Image</th>
                                    <th>Service Name</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($services)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted">No services available. Create your first service!</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($services as $svc): ?>
                                        <tr>
                                            <td>#<?php echo $svc['service_id']; ?></td>
                                            <td>
                                                <?php if (!empty($svc['image_url'])): ?>
                                                    <img src="<?php echo escape($svc['image_url']); ?>" 
                                                         class="rounded" 
                                                         style="width: 60px; height: 60px; object-fit: cover;"
                                                         alt="<?php echo escape($svc['service_name']); ?>">
                                                <?php else: ?>
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="bi bi-image text-white"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo escape($svc['service_name']); ?></strong>
                                                <?php if (!empty($svc['description'])): ?>
                                                    <br><small class="text-muted"><?php echo escape(substr($svc['description'], 0, 50)) . (strlen($svc['description']) > 50 ? '...' : ''); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><i class="bi bi-clock"></i> <?php echo $svc['duration']; ?> min</td>
                                            <td><strong class="text-primary">$<?php echo number_format($svc['price'], 2); ?></strong></td>
                                            <td>
                                                <span class="badge bg-<?php echo $svc['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($svc['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?php echo $svc['service_id']; ?>"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?php echo $svc['service_id']; ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal<?php echo $svc['service_id']; ?>"
                                                            title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Modals -->
            <?php if (!empty($services)): ?>
                <?php foreach ($services as $svc): ?>
                    <!-- View Modal -->
                    <div class="modal fade" id="viewModal<?php echo $svc['service_id']; ?>" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Service Details</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <?php if (!empty($svc['image_url'])): ?>
                                                <img src="<?php echo escape($svc['image_url']); ?>" 
                                                     class="img-fluid rounded mb-3" 
                                                     style="width: 100%; max-height: 300px; object-fit: cover;"
                                                     alt="<?php echo escape($svc['service_name']); ?>">
                                            <?php else: ?>
                                                <div class="bg-secondary rounded d-flex align-items-center justify-content-center mb-3" 
                                                     style="width: 100%; height: 300px;">
                                                    <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-7">
                                            <h4><?php echo escape($svc['service_name']); ?></h4>
                                            <span class="badge bg-<?php echo $svc['status'] == 'active' ? 'success' : 'secondary'; ?> mb-3">
                                                <?php echo ucfirst($svc['status']); ?>
                                            </span>
                                            
                                            <hr>
                                            
                                            <div class="mb-3">
                                                <strong><i class="bi bi-align-left"></i> Description:</strong>
                                                <p class="text-muted"><?php echo escape($svc['description']); ?></p>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-6">
                                                    <strong><i class="bi bi-clock"></i> Duration:</strong>
                                                    <p><?php echo $svc['duration']; ?> minutes</p>
                                                </div>
                                                <div class="col-6">
                                                    <strong><i class="bi bi-tag"></i> Price:</strong>
                                                    <p class="text-primary fs-5">$<?php echo number_format($svc['price'], 2); ?></p>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <strong><i class="bi bi-calendar-plus"></i> Created:</strong>
                                                <p><?php echo date('F d, Y', strtotime($svc['created_at'])); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" 
                                            data-bs-toggle="modal" data-bs-target="#editModal<?php echo $svc['service_id']; ?>">
                                        <i class="bi bi-pencil"></i> Edit Service
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
            <?php endforeach; ?>
            <?php foreach ($services as $svc): ?>
                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?php echo $svc['service_id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Service</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $svc['service_id']; ?>">
                                    
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="mb-3">
                                                <label class="form-label">Current Image</label>
                                                <div id="imagePreview<?php echo $svc['service_id']; ?>" class="mb-2">
                                                    <?php if (!empty($svc['image_url'])): ?>
                                                        <img src="<?php echo escape($svc['image_url']); ?>" 
                                                             class="img-fluid rounded" 
                                                             style="width: 100%; max-height: 250px; object-fit: cover;"
                                                             alt="Service Image">
                                                    <?php else: ?>
                                                        <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                                             style="width: 100%; height: 250px;">
                                                            <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <label class="form-label">Image URL</label>
                                                <input type="url" 
                                                       class="form-control" 
                                                       name="image_url" 
                                                       id="imageUrl<?php echo $svc['service_id']; ?>"
                                                       value="<?php echo escape($svc['image_url'] ?? ''); ?>" 
                                                       placeholder="https://example.com/image.jpg"
                                                       onchange="updateImagePreview<?php echo $svc['service_id']; ?>(this.value)">
                                                <small class="text-muted">Enter image URL or leave blank for default</small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-7">
                                            <div class="mb-3">
                                                <label class="form-label">Service Name *</label>
                                                <input type="text" class="form-control" name="service_name" 
                                                       value="<?php echo escape($svc['service_name']); ?>" required>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea class="form-control" name="description" rows="3"><?php echo escape($svc['description']); ?></textarea>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Duration (minutes) *</label>
                                                    <input type="number" class="form-control" name="duration" 
                                                           value="<?php echo $svc['duration']; ?>" min="15" step="15" required>
                                                </div>
                                                
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Price ($) *</label>
                                                    <input type="number" class="form-control" name="price" 
                                                           value="<?php echo $svc['price']; ?>" min="0" step="0.01" required>
                                                </div>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" name="status">
                                                    <option value="active" <?php echo $svc['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                    <option value="inactive" <?php echo $svc['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="save_service" class="btn btn-primary">
                                        <i class="bi bi-check-circle"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <script>
                function updateImagePreview<?php echo $svc['service_id']; ?>(url) {
                    const preview = document.getElementById('imagePreview<?php echo $svc['service_id']; ?>');
                    if (url) {
                        preview.innerHTML = '<img src="' + url + '" class="img-fluid rounded" style="width: 100%; max-height: 250px; object-fit: cover;" alt="Service Image" onerror="this.parentElement.innerHTML=\'<div class=\\\'bg-danger rounded d-flex align-items-center justify-content-center\\\' style=\\\'width: 100%; height: 250px;\\\'><i class=\\\'bi bi-exclamation-triangle text-white\\\' style=\\\'font-size: 3rem;\\\'></i></div>\';">';
                    } else {
                        preview.innerHTML = '<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100%; height: 250px;"><i class="bi bi-image text-white" style="font-size: 3rem;"></i></div>';
                    }
                }
                </script>
                
                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?php echo $svc['service_id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title">Delete Service</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="">
                                <div class="modal-body">
                                    <input type="hidden" name="service_id" value="<?php echo $svc['service_id']; ?>">
                                    <p class="text-danger"><strong>Warning!</strong> This action cannot be undone.</p>
                                    <p>Are you sure you want to delete <strong><?php echo escape($svc['service_name']); ?></strong>?</p>
                                    <p class="text-warning"><i class="bi bi-exclamation-triangle"></i> This may affect existing appointments using this service.</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="delete_service" class="btn btn-danger">
                                        <i class="bi bi-trash"></i> Delete Service
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Service Modal -->
<div class="modal fade" id="serviceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Service</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label class="form-label">Image Preview</label>
                                <div id="newServiceImagePreview" class="mb-2">
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center" 
                                         style="width: 100%; height: 250px;">
                                        <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                                    </div>
                                </div>
                                <label class="form-label">Image URL</label>
                                <input type="url" 
                                       class="form-control" 
                                       name="image_url" 
                                       id="newServiceImageUrl"
                                       placeholder="https://example.com/image.jpg"
                                       onchange="updateNewServiceImagePreview(this.value)">
                                <small class="text-muted">Enter image URL or leave blank for default</small>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <div class="mb-3">
                                <label class="form-label">Service Name *</label>
                                <input type="text" class="form-control" name="service_name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3" 
                                          placeholder="Brief description of the service..."></textarea>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration (minutes) *</label>
                                    <input type="number" class="form-control" name="duration" 
                                           value="60" min="15" step="15" required>
                                    <small class="text-muted">Increments of 15 minutes</small>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Price ($) *</label>
                                    <input type="number" class="form-control" name="price" 
                                           value="0.00" min="0" step="0.01" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="active" selected>Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="save_service" class="btn btn-primary">
                        <i class="bi bi-save"></i> Create Service
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateNewServiceImagePreview(url) {
    const preview = document.getElementById('newServiceImagePreview');
    if (url) {
        preview.innerHTML = '<img src="' + url + '" class="img-fluid rounded" style="width: 100%; max-height: 250px; object-fit: cover;" alt="Service Image" onerror="this.parentElement.innerHTML=\'<div class=\\\'bg-danger rounded d-flex align-items-center justify-content-center\\\' style=\\\'width: 100%; height: 250px;\\\'><i class=\\\'bi bi-exclamation-triangle text-white\\\' style=\\\'font-size: 3rem;\\\'></i></div>\';">';
    } else {
        preview.innerHTML = '<div class="bg-secondary rounded d-flex align-items-center justify-content-center" style="width: 100%; height: 250px;"><i class="bi bi-image text-white" style="font-size: 3rem;"></i></div>';
    }
}
</script>

<?php include '../includes/footer.php'; ?>
