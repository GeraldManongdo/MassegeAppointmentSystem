<?php
require_once 'config/config.php';

$page_title = "Register";

// If already logged in, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } else {
        redirect(SITE_URL . 'user/dashboard.php');
    }
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $terms = isset($_POST['terms']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!$terms) {
        $error = "Please accept the terms and conditions.";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        // Check if email already exists
        $user->email = $email;
        if ($user->emailExists()) {
            $error = "Email already registered. Please login or use a different email.";
        } else {
            // Create new user
            $user->full_name = $full_name;
            $user->email = $email;
            $user->phone = $phone;
            $user->password = $password;
            $user->role = 'user';
            $user->status = 'active';
            
            if ($user->create()) {
                redirect(SITE_URL . 'login.php?registered=1');
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-6 d-none d-md-block bg-primary text-white p-5">
            <div class="p-5">
                <h1 class="display-4 mb-4"><i class="bi bi-calendar-check"></i> Join Us Today</h1>
                <p class="lead">Create your account to start booking appointments with ease.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Quick & easy registration</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Book appointments 24/7</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Track your booking history</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Manage your profile</li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-6 p-5">
            <div class="mx-auto" style="max-width: 500px;">
                <h2 class="mb-4">Create New Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo isset($_POST['full_name']) ? escape($_POST['full_name']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small id="password-strength" class="form-text"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" 
                               name="confirm_password" required>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms & Conditions</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-person-plus"></i> Create Account
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted">Already have an account? 
                        <a href="login.php" class="text-decoration-none">Login here</a>
                    </p>
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
