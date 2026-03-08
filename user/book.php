<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn() || isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Book Appointment";

$database = new Database();
$db = $database->getConnection();
$service_model = new Service($db);
$appointment_model = new Appointment($db);

$error = '';
$success = '';

// Get service ID from GET or POST
$selected_service_id = null;
if (isset($_GET['service'])) {
    $selected_service_id = (int)$_GET['service'];
} elseif (isset($_POST['service_id'])) {
    $selected_service_id = (int)$_POST['service_id'];
}

// Get selected date from POST
$selected_date = isset($_POST['appointment_date']) ? $_POST['appointment_date'] : null;

// Determine current step
$step = isset($_GET['step']) ? (int)$_GET['step'] : ($selected_service_id ? 2 : 1);

$time_slots = [];

// Get service details if service is selected
$selected_service = null;
if ($selected_service_id) {
    $selected_service = $service_model->getById($selected_service_id);
    if (!$selected_service) {
        // Service not found, go back to step 1
        $step = 1;
        $selected_service_id = null;
        $error = "Service not found. Please select a service.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'select_date') {
        $selected_service_id = (int)$_POST['service_id'];
        $selected_date = $_POST['appointment_date'];
        
        // Load service details
        $selected_service = $service_model->getById($selected_service_id);
        
        // Validate date is not in the past
        if (strtotime($selected_date) < strtotime(date('Y-m-d'))) {
            $error = "Please select a future date.";
            $step = 2;
        } else {
            // Get available time slots
            $time_slots = $appointment_model->getAvailableTimeSlots($selected_date, $selected_service_id);
            if (empty($time_slots)) {
                $day_name = date('l', strtotime($selected_date));
                $error = "No available time slots for " . $day_name . ", " . formatDate($selected_date) . ". Please select a different date (Monday-Friday).";
                $step = 2;
            } else {
                $step = 3;
            }
        }
    } elseif ($_POST['action'] == 'confirm_booking') {
        $selected_service_id = (int)$_POST['service_id'];
        $selected_date = $_POST['appointment_date'];
        $selected_time = isset($_POST['selected_time']) ? $_POST['selected_time'] : '';
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
        
        // Load service details
        $selected_service = $service_model->getById($selected_service_id);
        
        // Validate time slot is selected
        if (empty($selected_time)) {
            $error = "Please select a time slot.";
            $time_slots = $appointment_model->getAvailableTimeSlots($selected_date, $selected_service_id);
            $step = 3;
        } else {
            // Parse start and end time
            $time_parts = explode('|', $selected_time);
            if (count($time_parts) != 2) {
                $error = "Invalid time slot selected.";
                $time_slots = $appointment_model->getAvailableTimeSlots($selected_date, $selected_service_id);
                $step = 3;
            } else {
                list($start_time, $end_time) = $time_parts;
                
                // Create appointment
                $appointment_model->user_id = getCurrentUserId();
                $appointment_model->service_id = $selected_service_id;
                $appointment_model->appointment_date = $selected_date;
                $appointment_model->start_time = $start_time;
                $appointment_model->end_time = $end_time;
                $appointment_model->status = 'confirmed';
                $appointment_model->notes = $notes;
                
                if ($appointment_model->create()) {
                    redirect(SITE_URL . 'user/appointments.php?booked=1');
                    exit();
                } else {
                    $error = "Failed to create appointment. Time slot may no longer be available.";
                    // Reload time slots
                    $time_slots = $appointment_model->getAvailableTimeSlots($selected_date, $selected_service_id);
                    $step = 3;
                }
            }
        }
    }
}

// Get all services for step 1
$services = $service_model->getAll('active');

// Add inline JavaScript for time slot selection
$extra_js = "
<script>
$(document).ready(function() {
    console.log('Booking page JS loaded');
    
    // Time slot selection
    $('.time-slot:not(.disabled)').on('click', function() {
        console.log('Time slot clicked');
        
        // Remove selected class from all slots
        $('.time-slot').removeClass('selected');
        
        // Add selected class to clicked slot
        $(this).addClass('selected');
        
        // Get and set the time value
        const timeValue = $(this).data('time');
        $('#selected_time_input').val(timeValue);
        
        // Enable the confirm button
        $('#confirmBtn').prop('disabled', false);
        
        console.log('Time slot selected:', timeValue);
        console.log('Button enabled');
    });
    
    // Form validation before submit
    $('#timeSlotForm').on('submit', function(e) {
        const selectedTime = $('#selected_time_input').val();
        
        if (!selectedTime || selectedTime === '') {
            e.preventDefault();
            alert('Please select a time slot before confirming your appointment.');
            return false;
        }
        
        // Show loading state
        $('#confirmBtn').html('<i class=\"bi bi-hourglass-split\"></i> Processing...').prop('disabled', true);
        
        return true;
    });
    
    // Highlight any pre-selected time slot on page load
    const preSelectedTime = $('#selected_time_input').val();
    if (preSelectedTime) {
        $('.time-slot[data-time=\"' + preSelectedTime + '\"]').addClass('selected');
        $('#confirmBtn').prop('disabled', false);
    }
});
</script>
";

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h2><i class="bi bi-calendar-plus"></i> Book Appointment</h2>
            <p class="text-muted">Schedule your massage therapy session in a few easy steps</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Progress Steps -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center position-relative">
                    <div class="progress-line bg-light position-absolute w-100" style="height: 3px; top: 20px; z-index: 0;"></div>
                    <div class="d-flex justify-content-between w-100 position-relative" style="z-index: 1;">
                        <div class="text-center">
                            <div class="step-circle <?php echo $step >= 1 ? 'active' : ''; ?> mb-2">
                                <i class="bi bi-<?php echo $step > 1 ? 'check-circle-fill' : '1-circle-fill'; ?>"></i>
                            </div>
                            <small class="d-block fw-semibold">Service</small>
                        </div>
                        <div class="text-center">
                            <div class="step-circle <?php echo $step >= 2 ? 'active' : ''; ?> mb-2">
                                <i class="bi bi-<?php echo $step > 2 ? 'check-circle-fill' : '2-circle-fill'; ?>"></i>
                            </div>
                            <small class="d-block fw-semibold">Date</small>
                        </div>
                        <div class="text-center">
                            <div class="step-circle <?php echo $step >= 3 ? 'active' : ''; ?> mb-2">
                                <i class="bi bi-<?php echo $step > 3 ? 'check-circle-fill' : '3-circle-fill'; ?>"></i>
                            </div>
                            <small class="d-block fw-semibold">Time</small>
                        </div>
                        <div class="text-center">
                            <div class="step-circle <?php echo $step >= 4 ? 'active' : ''; ?> mb-2">
                                <i class="bi bi-4-circle-fill"></i>
                            </div>
                            <small class="d-block fw-semibold">Confirm</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Step 1: Select Service -->
                    <?php if ($step == 1): ?>
                        <h5 class="mb-4"><i class="bi bi-spa"></i> Step 1: Select a Service</h5>
                        <div class="row g-4">
                            <?php foreach ($services as $svc): ?>
                                <div class="col-md-6">
                                    <div class="card service-card h-100 border-0 shadow-sm <?php echo $selected_service_id == $svc['service_id'] ? 'border border-primary border-2' : ''; ?>">
                                        <?php if (!empty($svc['image_url'])): ?>
                                            <img src="<?php echo escape($svc['image_url']); ?>" 
                                                 class="service-card-img" 
                                                 alt="<?php echo escape($svc['service_name']); ?>"
                                                 style="height: 150px;">
                                        <?php else: ?>
                                            <div class="service-card-placeholder" style="height: 150px;">
                                                <i class="bi bi-spa"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h6 class="text-primary mb-2">
                                                <i class="bi bi-spa"></i> <?php echo escape($svc['service_name']); ?>
                                            </h6>
                                            <p class="text-muted small mb-3"><?php echo escape(substr($svc['description'], 0, 100)); ?><?php echo strlen($svc['description']) > 100 ? '...' : ''; ?></p>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted small">
                                                    <i class="bi bi-clock"></i> <?php echo $svc['duration']; ?> minutes
                                                </span>
                                                <span class="text-primary fw-bold fs-5">$<?php echo number_format($svc['price'], 2); ?></span>
                                            </div>
                                            <a href="?service=<?php echo $svc['service_id']; ?>&step=2" 
                                               class="btn btn-primary w-100">
                                                <i class="bi bi-arrow-right-circle"></i> Select This Service
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Step 2: Select Date -->
                    <?php if ($step == 2 && $selected_service): ?>
                        <h5 class="mb-4"><i class="bi bi-calendar-event"></i> Step 2: Choose Date</h5>
                        
                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h6 class="text-primary mb-3"><i class="bi bi-check-circle"></i> Selected Service</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <h6 class="mb-1"><?php echo escape($selected_service['service_name']); ?></h6>
                                        <p class="text-muted small mb-0"><?php echo escape($selected_service['description']); ?></p>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <div class="mb-2">
                                            <i class="bi bi-clock text-primary"></i>
                                            <strong><?php echo $selected_service['duration']; ?></strong> minutes
                                        </div>
                                        <div>
                                            <i class="bi bi-tag text-primary"></i>
                                            <strong class="text-primary fs-5">$<?php echo number_format($selected_service['price'], 2); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="select_date">
                            <input type="hidden" name="service_id" value="<?php echo $selected_service_id; ?>">
                            
                            <div class="mb-4">
                                <label for="appointment_date" class="form-label fw-semibold">
                                    <i class="bi bi-calendar3"></i> Select Appointment Date *
                                </label>
                                <input type="date" class="form-control form-control-lg" id="appointment_date" 
                                       name="appointment_date" 
                                       min="<?php echo date('Y-m-d'); ?>"
                                       max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>"
                                       value="<?php echo $selected_date; ?>"
                                       required>
                                <div class="form-text">
                                    <i class="bi bi-info-circle"></i> Business hours: Monday - Friday, 9:00 AM - 5:00 PM
                                </div>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="?step=1" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left"></i> Back to Services
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    Continue <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                    
                    <!-- Step 3: Select Time Slot -->
                    <?php if ($step == 3): ?>
                        <?php if ($selected_service && !empty($time_slots)): ?>
                            <h5 class="mb-4"><i class="bi bi-clock"></i> Step 3: Select Time Slot</h5>
                            
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary mb-2"><i class="bi bi-calendar-event"></i> Appointment Details</h6>
                                            <div class="mb-1">
                                                <strong>Date:</strong> <?php echo formatDate($selected_date); ?>
                                            </div>
                                            <div>
                                                <strong>Service:</strong> <?php echo escape($selected_service['service_name']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <div class="mb-1">
                                                <i class="bi bi-clock text-primary"></i>
                                                <strong><?php echo $selected_service['duration']; ?></strong> minutes
                                            </div>
                                            <div>
                                                <i class="bi bi-tag text-primary"></i>
                                                <strong class="text-primary fs-5">$<?php echo number_format($selected_service['price'], 2); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <form method="POST" action="" id="timeSlotForm">
                                <input type="hidden" name="action" value="confirm_booking">
                                <input type="hidden" name="service_id" value="<?php echo $selected_service_id; ?>">
                                <input type="hidden" name="appointment_date" value="<?php echo $selected_date; ?>">
                                <input type="hidden" name="selected_time" id="selected_time_input">
                                
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">
                                        <i class="bi bi-clock-history"></i> Choose Your Preferred Time *
                                    </label>
                                    <div class="row g-3">
                                        <?php 
                                        $available_count = 0;
                                        foreach ($time_slots as $slot): 
                                            if ($slot['available']) $available_count++;
                                        ?>
                                            <div class="col-md-4">
                                                <div class="time-slot p-3 text-center rounded <?php echo !$slot['available'] ? 'disabled' : ''; ?>" 
                                                     data-time="<?php echo $slot['start_time'] . '|' . $slot['end_time']; ?>"
                                                     <?php echo !$slot['available'] ? 'title="Not available"' : ''; ?>>
                                                    <i class="bi bi-clock"></i> <?php echo $slot['display_time']; ?>
                                                    <?php if (!$slot['available']): ?>
                                                        <br><small class="text-muted">Booked</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if ($available_count == 0): ?>
                                        <div class="alert alert-warning mt-3">
                                            <i class="bi bi-exclamation-triangle"></i> No available time slots for this date. Please select a different date.
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="notes" class="form-label fw-semibold">
                                        <i class="bi bi-chat-left-text"></i> Additional Notes (Optional)
                                    </label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Any special requests, health concerns, or preferences we should know about..."></textarea>
                                    <div class="form-text">This information helps us provide better service</div>
                                </div>
                                
                                <div class="d-flex gap-2">
                                    <a href="?service=<?php echo $selected_service_id; ?>&step=2" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-left"></i> Back to Date
                                    </a>
                                    <button type="submit" class="btn btn-success px-4 flex-grow-1" id="confirmBtn" disabled>
                                        <i class="bi bi-check-circle"></i> Confirm Booking
                                    </button>
                                </div>
                            </form>
                        <?php elseif (empty($time_slots)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No time slots available. The business may be closed on this date.
                            </div>
                            <a href="?service=<?php echo $selected_service_id; ?>&step=2" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Choose Different Date
                            </a>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle"></i> Invalid booking data. Please start over.
                            </div>
                            <a href="?step=1" class="btn btn-primary">
                                <i class="bi bi-arrow-left"></i> Start Over
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
