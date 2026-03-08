<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Manage Bookings";

$database = new Database();
$db = $database->getConnection();
$appointment_model = new Appointment($db);
$service_model = new Service($db);

$success = '';
$error = '';

// Handle status update
if (isset($_POST['update_status'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $new_status = $_POST['status'];
    
    if ($appointment_model->updateStatus($appointment_id, $new_status)) {
        $success = "Appointment status updated successfully!";
    } else {
        $error = "Failed to update appointment status.";
    }
}

// Handle cancel appointment
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $reason = trim($_POST['cancellation_reason']);
    
    if ($appointment_model->cancel($appointment_id, $reason)) {
        $success = "Appointment cancelled successfully!";
    } else {
        $error = "Failed to cancel appointment.";
    }
}

// Handle update appointment
if (isset($_POST['update_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $appointment_date = $_POST['appointment_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);
    
    if (empty($appointment_date) || empty($start_time) || empty($end_time)) {
        $error = "Please fill in all required fields.";
    } else {
        $appointment_model->appointment_id = $appointment_id;
        $appointment_model->appointment_date = $appointment_date;
        $appointment_model->start_time = $start_time;
        $appointment_model->end_time = $end_time;
        $appointment_model->status = $status;
        $appointment_model->notes = $notes;
        
        if ($appointment_model->update()) {
            $success = "Appointment updated successfully!";
        } else {
            $error = "Failed to update appointment. Time slot may be conflicting.";
        }
    }
}

// Get filters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_date = isset($_GET['date']) ? $_GET['date'] : '';
$filter_service = isset($_GET['service_id']) ? (int)$_GET['service_id'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build filters array
$filters = [];
if ($filter_status) $filters['status'] = $filter_status;
if ($filter_date) $filters['date'] = $filter_date;
if ($filter_service) $filters['service_id'] = $filter_service;

// Get all appointments
$appointments = $appointment_model->getAll($filters);

// Filter by search (name, email, phone)
if ($search) {
    $appointments = array_filter($appointments, function($app) use ($search) {
        $search_lower = strtolower($search);
        return stripos($app['full_name'], $search) !== false ||
               stripos($app['email'], $search) !== false ||
               stripos($app['phone'], $search) !== false;
    });
}

// Get all services for filter dropdown
$services = $service_model->getAll();

include '../includes/header.php';
?>

<div class="admin-layout d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <div class="admin-content flex-grow-1">
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="bi bi-calendar3"></i> Manage Bookings</h2>
                    <p class="text-muted">View and manage all appointments</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-secondary" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print Report
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
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Name, Email, or Phone..." 
                                   value="<?php echo escape($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" name="date" 
                                   value="<?php echo escape($filter_date); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Service</label>
                            <select class="form-select" name="service_id">
                                <option value="">All Services</option>
                                <?php foreach ($services as $service): ?>
                                    <option value="<?php echo $service['service_id']; ?>" 
                                            <?php echo $filter_service == $service['service_id'] ? 'selected' : ''; ?>>
                                        <?php echo escape($service['service_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="bookings.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Bookings Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Appointments List 
                        <span class="badge bg-primary"><?php echo count($appointments); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Service</th>
                                    <th>Date & Time</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
<tbody>
                                <?php if (empty($appointments)): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted">No appointments found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($appointments as $appointment): ?>
                                        <tr>
                                            <td>#<?php echo $appointment['appointment_id']; ?></td>
                                            <td>
                                                <strong><?php echo escape($appointment['full_name']); ?></strong>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="bi bi-envelope"></i> <?php echo escape($appointment['email']); ?><br>
                                                    <i class="bi bi-telephone"></i> <?php echo escape($appointment['phone']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo escape($appointment['service_name']); ?></td>
                                            <td>
                                                <i class="bi bi-calendar3"></i> <?php echo formatDate($appointment['appointment_date']); ?><br>
                                                <i class="bi bi-clock"></i> <?php echo formatTime($appointment['start_time']); ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $start = new DateTime($appointment['start_time']);
                                                $end = new DateTime($appointment['end_time']);
                                                $duration = $start->diff($end);
                                                echo $duration->h . 'h ' . $duration->i . 'm';
                                                ?>
                                            </td>
                                            <td>$<?php echo number_format($appointment['price'], 2); ?></td>
                                            <td>
                                                <?php
                                                $badge_class = [
                                                    'pending' => 'warning',
                                                    'confirmed' => 'info',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $class = $badge_class[$appointment['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $class; ?>">
                                                    <?php echo ucfirst($appointment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?php echo $appointment['appointment_id']; ?>"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?php echo $appointment['appointment_id']; ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($appointment['status'] != 'cancelled'): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#cancelModal<?php echo $appointment['appointment_id']; ?>"
                                                            title="Cancel">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                    <?php endif; ?>
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
            
            <?php if (!empty($appointments)): ?>
                <?php foreach ($appointments as $appointment): ?>
                    <div class="modal fade" id="viewModal<?php echo $appointment['appointment_id']; ?>" tabindex="1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Appointment Details #<?php echo $appointment['appointment_id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Customer:</strong><br>
                                            <?php echo escape($appointment['full_name']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Status:</strong><br>
                                            <span class="badge bg-<?php echo $badge_class[$appointment['status']]; ?>">
                                                <?php echo ucfirst($appointment['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Email:</strong><br>
                                            <?php echo escape($appointment['email']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Phone:</strong><br>
                                            <?php echo escape($appointment['phone']); ?>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <strong>Service:</strong><br>
                                        <?php echo escape($appointment['service_name']); ?>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <strong>Date:</strong><br>
                                            <?php echo formatDate($appointment['appointment_date']); ?>
                                        </div>
                                        <div class="col-6">
                                            <strong>Time:</strong><br>
                                            <?php echo formatTime($appointment['start_time']); ?> - <?php echo formatTime($appointment['end_time']); ?>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Price:</strong><br>
                                        $<?php echo number_format($appointment['price'], 2); ?>
                                    </div>
                                    <?php if (!empty($appointment['notes'])): ?>
                                    <div class="mb-3">
                                        <strong>Notes:</strong><br>
                                        <?php echo nl2br(escape($appointment['notes'])); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($appointment['cancellation_reason'])): ?>
                                    <div class="alert alert-warning">
                                        <strong>Cancellation Reason:</strong><br>
                                        <?php echo escape($appointment['cancellation_reason']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="editModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Appointment #<?php echo $appointment['appointment_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Date *</label>
                                            <input type="date" class="form-control" name="appointment_date" 
                                                   value="<?php echo $appointment['appointment_date']; ?>" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-6 mb-3">
                                                <label class="form-label">Start Time *</label>
                                                <input type="time" class="form-control" name="start_time" 
                                                       value="<?php echo $appointment['start_time']; ?>" required>
                                            </div>
                                            <div class="col-6 mb-3">
                                                <label class="form-label">End Time *</label>
                                                <input type="time" class="form-control" name="end_time" 
                                                       value="<?php echo $appointment['end_time']; ?>" required>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="pending" <?php echo $appointment['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                <option value="confirmed" <?php echo $appointment['status'] == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                                <option value="completed" <?php echo $appointment['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="cancelled" <?php echo $appointment['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Notes</label>
                                            <textarea class="form-control" name="notes" rows="3"><?php echo escape($appointment['notes']); ?></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="update_appointment" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Update Appointment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="cancelModal<?php echo $appointment['appointment_id']; ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="">
                                    <div class="modal-header bg-warning">
                                        <h5 class="modal-title">Cancel Appointment</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                        <p>Are you sure you want to cancel this appointment?</p>
                                        <p><strong>Customer:</strong> <?php echo escape($appointment['full_name']); ?><br>
                                           <strong>Service:</strong> <?php echo escape($appointment['service_name']); ?><br>
                                           <strong>Date:</strong> <?php echo formatDate($appointment['appointment_date']); ?> at <?php echo formatTime($appointment['start_time']); ?>
                                        </p>
                                        <div class="mb-3">
                                            <label class="form-label">Cancellation Reason</label>
                                            <textarea class="form-control" name="cancellation_reason" rows="3" 
                                                      placeholder="Enter reason for cancellation..."></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="cancel_appointment" class="btn btn-warning">
                                            <i class="bi bi-x-circle"></i> Cancel Appointment
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

<?php include '../includes/footer.php'; ?>