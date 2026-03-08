<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn() || isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Dashboard";

$database = new Database();
$db = $database->getConnection();

// Get user stats
$appointment = new Appointment($db);
$service = new Service($db);

$user_id = getCurrentUserId();
$upcoming_appointment = $appointment->getUpcomingAppointment($user_id);
$all_appointments = $appointment->getUserAppointments($user_id);
$services = $service->getAll('active');

// Count appointments by status
$total_appointments = count($all_appointments);
$confirmed_count = count(array_filter($all_appointments, function($app) {
    return $app['status'] == 'confirmed';
}));
$completed_count = count(array_filter($all_appointments, function($app) {
    return $app['status'] == 'completed';
}));

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-house"></i> Welcome, <?php echo escape($_SESSION['full_name']); ?>!</h2>
            <p class="text-muted">Manage your appointments and book new services</p>
        </div>
    </div>
    
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Total Appointments</div>
                    <div class="stat-value text-primary"><?php echo $total_appointments; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="stat-label">Upcoming</div>
                    <div class="stat-value text-success"><?php echo $confirmed_count; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="stat-label">Completed</div>
                    <div class="stat-value text-info"><?php echo $completed_count; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="stat-label">Available Services</div>
                    <div class="stat-value text-secondary"><?php echo count($services); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row g-4">
        <!-- Upcoming Appointment -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-calendar-event"></i> Next Appointment</h5>
                </div>
                <div class="card-body">
                    <?php if ($upcoming_appointment): ?>
                        <h6 class="text-primary"><?php echo escape($upcoming_appointment['service_name']); ?></h6>
                        <p class="mb-2">
                            <i class="bi bi-calendar"></i> 
                            <?php echo formatDate($upcoming_appointment['appointment_date']); ?>
                        </p>
                        <p class="mb-2">
                            <i class="bi bi-clock"></i> 
                            <?php echo formatTime($upcoming_appointment['start_time']); ?> - 
                            <?php echo formatTime($upcoming_appointment['end_time']); ?>
                        </p>
                        <p class="mb-3">
                            <i class="bi bi-hourglass"></i> 
                            Duration: <?php echo $upcoming_appointment['duration']; ?> minutes
                        </p>
                        <a href="appointments.php" class="btn btn-sm btn-outline-primary">
                            View Details <i class="bi bi-arrow-right"></i>
                        </a>
                    <?php else: ?>
                        <p class="text-muted mb-3">You don't have any upcoming appointments.</p>
                        <a href="services.php" class="btn btn-primary">
                            <i class="bi bi-calendar-plus"></i> Book Appointment
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="services.php" class="btn btn-primary">
                            <i class="bi bi-calendar-plus"></i> Book New Appointment
                        </a>
                        <a href="appointments.php" class="btn btn-outline-secondary">
                            <i class="bi bi-calendar-event"></i> View My Appointments
                        </a>
                        <a href="profile.php" class="btn btn-outline-secondary">
                            <i class="bi bi-person"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Appointments -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Appointments</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($all_appointments)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Service</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($all_appointments, 0, 5) as $app): ?>
                                        <tr>
                                            <td><?php echo escape($app['service_name']); ?></td>
                                            <td><?php echo formatDate($app['appointment_date']); ?></td>
                                            <td><?php echo formatTime($app['start_time']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    echo $app['status'] == 'confirmed' ? 'success' : 
                                                        ($app['status'] == 'completed' ? 'primary' : 
                                                        ($app['status'] == 'cancelled' ? 'danger' : 'warning')); 
                                                ?>">
                                                    <?php echo ucfirst($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="appointments.php" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (count($all_appointments) > 5): ?>
                            <div class="text-center mt-3">
                                <a href="appointments.php" class="btn btn-outline-primary">
                                    View All Appointments <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-center text-muted mb-0">No appointments yet. Start booking now!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
