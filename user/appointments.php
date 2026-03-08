<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn() || isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "My Appointments";

$database = new Database();
$db = $database->getConnection();
$appointment = new Appointment($db);

$success = '';
$error = '';

if (isset($_GET['booked'])) {
    $success = "Appointment booked successfully!";
}

if (isset($_GET['cancelled'])) {
    $success = "Appointment cancelled successfully.";
}

// Handle cancellation
if (isset($_POST['cancel_appointment'])) {
    $appointment_id = (int)$_POST['appointment_id'];
    $reason = trim($_POST['cancellation_reason']);
    
    // Get appointment details to check if user owns it
    $app_details = $appointment->getById($appointment_id);
    
    if ($app_details && $app_details['user_id'] == getCurrentUserId()) {
        // Check cancellation policy (24 hours before)
        $appointment_datetime = strtotime($app_details['appointment_date'] . ' ' . $app_details['start_time']);
        $hours_until = ($appointment_datetime - time()) / 3600;
        
        if ($hours_until < 24 && $hours_until > 0) {
            $error = "Appointments can only be cancelled at least 24 hours in advance.";
        } elseif ($hours_until < 0) {
            $error = "Cannot cancel past appointments.";
        } else {
            if ($appointment->cancel($appointment_id, $reason)) {
                redirect(SITE_URL . 'user/appointments.php?cancelled=1');
            } else {
                $error = "Failed to cancel appointment. Please try again.";
            }
        }
    } else {
        $error = "Invalid appointment.";
    }
}

// Get user appointments
$user_id = getCurrentUserId();
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

if ($filter == 'upcoming') {
    $appointments = array_filter($appointment->getUserAppointments($user_id), function($app) {
        return $app['status'] == 'confirmed' && strtotime($app['appointment_date']) >= strtotime(date('Y-m-d'));
    });
} elseif ($filter == 'completed') {
    $appointments = $appointment->getUserAppointments($user_id, 'completed');
} elseif ($filter == 'cancelled') {
    $appointments = $appointment->getUserAppointments($user_id, 'cancelled');
} else {
    $appointments = $appointment->getUserAppointments($user_id);
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2><i class="bi bi-calendar-event"></i> My Appointments</h2>
            <p class="text-muted">View and manage your appointments</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="services.php" class="btn btn-primary">
                <i class="bi bi-calendar-plus"></i> Book New Appointment
            </a>
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
    
    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo $filter == 'all' ? 'active' : ''; ?>" href="?filter=all">
                <i class="bi bi-list"></i> All
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter == 'upcoming' ? 'active' : ''; ?>" href="?filter=upcoming">
                <i class="bi bi-calendar-check"></i> Upcoming
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter == 'completed' ? 'active' : ''; ?>" href="?filter=completed">
                <i class="bi bi-check-circle"></i> Completed
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $filter == 'cancelled' ? 'active' : ''; ?>" href="?filter=cancelled">
                <i class="bi bi-x-circle"></i> Cancelled
            </a>
        </li>
    </ul>
    
    <!-- Appointments List -->
    <?php if (!empty($appointments)): ?>
        <div class="row g-4">
            <?php foreach ($appointments as $app): ?>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-spa text-primary"></i> 
                                    <?php echo escape($app['service_name']); ?>
                                </h5>
                                <span class="badge bg-<?php 
                                    echo $app['status'] == 'confirmed' ? 'success' : 
                                        ($app['status'] == 'completed' ? 'primary' : 
                                        ($app['status'] == 'cancelled' ? 'danger' : 'warning')); 
                                ?>">
                                    <?php echo ucfirst($app['status']); ?>
                                </span>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-calendar text-muted"></i> 
                                <?php echo formatDate($app['appointment_date']); ?>
                            </div>
                            
                            <div class="mb-2">
                                <i class="bi bi-clock text-muted"></i> 
                                <?php echo formatTime($app['start_time']); ?> - 
                                <?php echo formatTime($app['end_time']); ?>
                            </div>
                            
                            <div class="mb-3">
                                <i class="bi bi-hourglass text-muted"></i> 
                                <?php echo $app['duration']; ?> minutes
                            </div>
                            
                            <?php if ($app['notes']): ?>
                                <div class="alert alert-light mb-3">
                                    <small><strong>Notes:</strong> <?php echo escape($app['notes']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($app['status'] == 'cancelled' && $app['cancellation_reason']): ?>
                                <div class="alert alert-warning mb-3">
                                    <small><strong>Cancellation Reason:</strong> <?php echo escape($app['cancellation_reason']); ?></small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="d-flex gap-2">
                                <span class="text-primary fw-bold">$<?php echo number_format($app['price'], 2); ?></span>
                                
                                <?php if ($app['status'] == 'confirmed' && strtotime($app['appointment_date']) >= strtotime(date('Y-m-d'))): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-auto" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#cancelModal<?php echo $app['appointment_id']; ?>">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Cancel Modals - Placed outside the loop to prevent blinking -->
        <?php foreach ($appointments as $app): ?>
            <?php if ($app['status'] == 'confirmed' && strtotime($app['appointment_date']) >= strtotime(date('Y-m-d'))): ?>
                <div class="modal fade" id="cancelModal<?php echo $app['appointment_id']; ?>" tabindex="-1" aria-labelledby="cancelModalLabel<?php echo $app['appointment_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cancelModalLabel<?php echo $app['appointment_id']; ?>">Cancel Appointment</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="">
                                <div class="modal-body">
                                    <input type="hidden" name="appointment_id" value="<?php echo $app['appointment_id']; ?>">
                                    <p>Are you sure you want to cancel this appointment?</p>
                                    <p class="text-muted">
                                        <strong><?php echo escape($app['service_name']); ?></strong><br>
                                        <?php echo formatDate($app['appointment_date']); ?> at 
                                        <?php echo formatTime($app['start_time']); ?>
                                    </p>
                                    <div class="mb-3">
                                        <label for="cancellation_reason<?php echo $app['appointment_id']; ?>" class="form-label">Reason for Cancellation (Optional)</label>
                                        <textarea class="form-control" 
                                                  id="cancellation_reason<?php echo $app['appointment_id']; ?>"
                                                  name="cancellation_reason" 
                                                  rows="3" 
                                                  placeholder="Please provide a reason..."></textarea>
                                    </div>
                                    <div class="alert alert-warning">
                                        <small><i class="bi bi-info-circle"></i> Cancellation policy: 24 hours notice required</small>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="cancel_appointment" class="btn btn-danger">
                                        <i class="bi bi-x-circle"></i> Confirm Cancellation
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted"></i>
                <h5 class="mt-3">No Appointments Found</h5>
                <p class="text-muted">You don't have any <?php echo $filter == 'all' ? '' : $filter; ?> appointments.</p>
                <a href="services.php" class="btn btn-primary">
                    <i class="bi bi-calendar-plus"></i> Book Your First Appointment
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
