<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Admin Dashboard";

$database = new Database();
$db = $database->getConnection();

$appointment_model = new Appointment($db);
$user_model = new User($db);
$service_model = new Service($db);

// Get statistics
$stats = $appointment_model->getStatistics();
$total_users = $user_model->getCount();
$total_services = $service_model->getCount();

// Get today's appointments
$today_appointments = $appointment_model->getAll(['date' => date('Y-m-d')]);

// Get recent appointments
$recent_appointments = array_slice($appointment_model->getAll([]), 0, 10);

include '../includes/header.php';
?>

<div class="admin-layout d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <div class="admin-content flex-grow-1">
        <div class="container-fluid  p-4">
            <div class="row mb-4">
                <div class="col">
                    <h2><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
                    <p class="text-muted">Welcome back, <?php echo escape($_SESSION['full_name']); ?>!</p>
                </div>
            </div>
    
            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Total Bookings</div>
                                    <div class="stat-value text-primary"><?php echo $stats['total']; ?></div>
                                </div>
                                <div class="display-4 text-primary opacity-25">
                                    <i class="bi bi-calendar-check"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="col-md-3">
                    <div class="card stat-card success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Today's Appointments</div>
                                    <div class="stat-value text-success"><?php echo $stats['today']; ?></div>
                                </div>
                                <div class="display-4 text-success opacity-25">
                                    <i class="bi bi-calendar-event"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="col-md-3">
                    <div class="card stat-card warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Pending</div>
                                    <div class="stat-value text-warning"><?php echo $stats['pending']; ?></div>
                                </div>
                                <div class="display-4 text-warning opacity-25">
                                    <i class="bi bi-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Total Users</div>
                                    <div class="stat-value text-info"><?php echo $total_users; ?></div>
                                </div>
                                <div class="display-4 text-info opacity-25">
                                    <i class="bi bi-people"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
            <div class="row g-4">
                <!-- Today's Appointments -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-calendar-day"></i> Today's Appointments</h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($today_appointments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Customer</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($today_appointments as $app): ?>
                                                <tr>
                                                    <td><?php echo formatTime($app['start_time']); ?></td>
                                                    <td><?php echo escape($app['full_name']); ?></td>
                                                    <td><?php echo escape($app['service_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php 
                                                            echo $app['status'] == 'confirmed' ? 'success' : 
                                                                ($app['status'] == 'completed' ? 'primary' : 'warning'); 
                                                        ?>">
                                                            <?php echo ucfirst($app['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="bookings.php?view=<?php echo $app['appointment_id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted mb-0">No appointments scheduled for today.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
        <!-- Quick Stats -->
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-graph-up"></i> Quick Stats</h6>
                </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <span>Active Services</span>
                                <strong class="text-primary"><?php echo $total_services; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Completed This Month</span>
                                <strong class="text-success"><?php echo $stats['completed_month']; ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Registered Users</span>
                                <strong class="text-info"><?php echo $total_users; ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="bookings.php" class="btn btn-primary btn-sm">
                                    <i class="bi bi-calendar3"></i> Manage Bookings
                                </a>
                                <a href="services.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-grid"></i> Manage Services
                                </a>
                                <a href="users.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-people"></i> Manage Users
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Appointments</h5>
                            <a href="bookings.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_appointments)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Customer</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_appointments as $app): ?>
                                                <tr>
                                                    <td>#<?php echo $app['appointment_id']; ?></td>
                                                    <td><?php echo formatDate($app['appointment_date']); ?></td>
                                                    <td><?php echo formatTime($app['start_time']); ?></td>
                                                    <td><?php echo escape($app['full_name']); ?></td>
                                                    <td><?php echo escape($app['service_name']); ?></td>
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
                                                        <a href="bookings.php?view=<?php echo $app['appointment_id']; ?>" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            View
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted mb-0">No appointments yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
