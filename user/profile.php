<?php
require_once '../config/config.php';

// Check if user is logged in
if (!isLoggedIn() || isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Profile";

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

$success = '';
$error = '';

$user_id = getCurrentUserId();
$user = $user_model->getById($user_id);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Check if email is already taken by another user
        $user_model->email = $email;
        if ($user_model->emailExists() && $user_model->user_id != $user_id) {
            $error = "Email is already registered to another account.";
        } else {
            $user_model->user_id = $user_id;
            $user_model->full_name = $full_name;
            $user_model->email = $email;
            $user_model->phone = $phone;
            
            if ($user_model->update()) {
                // Update session
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $success = "Profile updated successfully!";
                $user = $user_model->getById($user_id); // Refresh user data
            } else {
                $error = "Failed to update profile. Please try again.";
            }
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($user_model->updatePassword($user_id, $new_password)) {
                $success = "Password changed successfully!";
            } else {
                $error = "Failed to change password. Please try again.";
            }
        } else {
            $error = "Current password is incorrect.";
        }
    }
}

include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Profile Sidebar -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="display-1 text-primary mb-3">
                        <i class="bi bi-person-circle"></i>
                    </div>
                    <h5><?php echo escape($user['full_name']); ?></h5>
                    <p class="text-muted mb-0"><?php echo escape($user['email']); ?></p>
                    <hr>
                    <small class="text-muted">
                        Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                    </small>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-3 text-muted">Quick Links</h6>
                    <div class="d-grid gap-2">
                        <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                        <a href="appointments.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-calendar-event"></i> My Appointments
                        </a>
                        <a href="services.php" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-grid"></i> Browse Services
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2 class="mb-4"><i class="bi bi-person"></i> My Profile</h2>
            
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
            
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="full_name" name="full_name" 
                                       value="<?php echo escape($user['full_name']); ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo escape($user['email']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo escape($user['phone']); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="bi bi-save"></i> Save Changes
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Change Password -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password *</label>
                            <input type="password" class="form-control" id="current_password" 
                                   name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password *</label>
                            <input type="password" class="form-control" id="new_password" 
                                   name="new_password" required>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
