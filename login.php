<?php
require_once 'config/config.php';

$page_title = "Login";

// If already logged in, redirect to appropriate dashboard
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . 'admin/dashboard.php');
    } else {
        redirect(SITE_URL . 'user/dashboard.php');
    }
}

$error = '';
$success = '';

if (isset($_GET['registered'])) {
    $success = "Registration successful! Please login to continue.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        
        $user->email = $email;
        
        if ($user->emailExists()) {
            if ($user->status !== 'active') {
                $error = "Your account has been suspended. Please contact administrator.";
            } elseif (password_verify($password, $user->password)) {
                // Set session variables
                $_SESSION['user_id'] = $user->user_id;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['email'] = $user->email;
                $_SESSION['role'] = $user->role;
                
                // Redirect based on role
                if ($user->role === 'admin') {
                    redirect(SITE_URL . 'admin/dashboard.php');
                } else {
                    redirect(SITE_URL . 'user/dashboard.php');
                }
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row min-vh-100 align-items-center">
        <div class="col-md-6 d-none d-md-block bg-primary text-white p-5">
            <div class="p-5">
                <h1 class="display-4 mb-4"><i class="bi bi-calendar-check"></i> Appointment System</h1>
                <p class="lead">Welcome back! Login to manage your appointments and services.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Easy appointment booking</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Real-time availability</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Manage your schedule</li>
                    <li class="mb-3"><i class="bi bi-check-circle-fill me-2"></i> Professional services</li>
                </ul>
            </div>
        </div>
        
        <div class="col-md-6 p-5">
            <div class="mx-auto" style="max-width: 400px;">
                <h2 class="mb-4">Login to Your Account</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> <?php echo escape($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> <?php echo escape($success); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>" 
                               required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted">Don't have an account? 
                        <a href="register.php" class="text-decoration-none">Register here</a>
                    </p>
                    <a href="index.php" class="text-decoration-none">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                </div>
                
                <div class="mt-4 p-3 bg-light rounded">
                    <small class="text-muted">
                        <strong>Demo Credentials:</strong><br>
                        Admin: admin@appointmentsystem.com / admin123<br>
                        User: Create a new account
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
