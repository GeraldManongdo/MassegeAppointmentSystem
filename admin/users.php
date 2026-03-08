<?php
require_once '../config/config.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(SITE_URL . 'login.php');
}

$page_title = "Manage Users";

$database = new Database();
$db = $database->getConnection();
$user_model = new User($db);

$success = '';
$error = '';

// Handle create/update user
if (isset($_POST['save_user'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $password = trim($_POST['password']);
    
    if (empty($full_name) || empty($email) || empty($phone)) {
        $error = "Please fill in all required fields.";
    } else {
        if ($user_id > 0) {
            // Update existing user
            $user_model->user_id = $user_id;
            $user_model->full_name = $full_name;
            $user_model->email = $email;
            $user_model->phone = $phone;
            
            if ($user_model->update()) {
                // Update role and status separately
                $query = "UPDATE users SET role = :role, status = :status WHERE user_id = :user_id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':role', $role);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->execute();
                
                // Update password if provided
                if (!empty($password)) {
                    $user_model->updatePassword($user_id, $password);
                }
                
                $success = "User updated successfully!";
            } else {
                $error = "Failed to update user.";
            }
        } else {
            // Create new user
            if (empty($password)) {
                $error = "Password is required for new users.";
            } else {
                $user_model->full_name = $full_name;
                $user_model->email = $email;
                $user_model->phone = $phone;
                $user_model->password = $password;
                $user_model->role = $role;
                $user_model->status = $status;
                
                // Check if email already exists
                $temp_user = new User($db);
                $temp_user->email = $email;
                if ($temp_user->emailExists()) {
                    $error = "Email already exists!";
                } else {
                    if ($user_model->create()) {
                        $success = "User created successfully!";
                    } else {
                        $error = "Failed to create user.";
                    }
                }
            }
        }
    }
}

// Handle status update
if (isset($_POST['update_status'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = $_POST['status'];
    
    if ($user_model->updateStatus($user_id, $new_status)) {
        $action = $new_status == 'active' ? 'activated' : 'deactivated';
        $success = "User $action successfully!";
    } else {
        $error = "Failed to update user status.";
    }
}

// Get filters
$filter_role = isset($_GET['role']) ? $_GET['role'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all users based on filters
if ($filter_role) {
    $users = $user_model->getAll($filter_role);
} else {
    $users = $user_model->getAll();
}

// Filter by status
if ($filter_status) {
    $users = array_filter($users, function($user) use ($filter_status) {
        return $user['status'] == $filter_status;
    });
}

// Filter by search
if ($search) {
    $users = array_filter($users, function($user) use ($search) {
        return stripos($user['full_name'], $search) !== false ||
               stripos($user['email'], $search) !== false ||
               stripos($user['phone'], $search) !== false;
    });
}

include '../includes/header.php';
?>

<div class="admin-layout d-flex">
    <?php include '../includes/admin_sidebar.php'; ?>
    
    <div class="admin-content flex-grow-1">
        <div class="container-fluid p-4">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h2><i class="bi bi-people"></i> Manage Users</h2>
                    <p class="text-muted">View and manage all system users</p>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus-circle"></i> Add New User
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
            
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Users</h6>
                            <h3><?php echo count($user_model->getAll()); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Active Users</h6>
                            <h3><?php echo count(array_filter($user_model->getAll(), fn($u) => $u['status'] == 'active')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Customers</h6>
                            <h3><?php echo count($user_model->getAll('user')); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Admins</h6>
                            <h3><?php echo count($user_model->getAll('admin')); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Name, Email, or Phone..." 
                                   value="<?php echo escape($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role">
                                <option value="">All Roles</option>
                                <option value="admin" <?php echo $filter_role == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="user" <?php echo $filter_role == 'user' ? 'selected' : ''; ?>>User</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $filter_status == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <a href="users.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Users List 
                        <span class="badge bg-primary"><?php echo count($users); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Registered</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted">No users found</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td>#<?php echo $user['user_id']; ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                        <span><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                                                    </div>
                                                    <strong><?php echo escape($user['full_name']); ?></strong>
                                                </div>
                                            </td>
                                            <td>
                                                <i class="bi bi-envelope"></i> <?php echo escape($user['email']); ?>
                                                <?php if (isset($user['email_verified']) && $user['email_verified']): ?>
                                                    <i class="bi bi-patch-check-fill text-success" title="Verified"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><i class="bi bi-telephone"></i> <?php echo escape($user['phone']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst($user['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small><?php echo isset($user['created_at']) ? formatDate($user['created_at']) : 'N/A'; ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" 
                                                            class="btn btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#viewModal<?php echo $user['user_id']; ?>"
                                                            title="View Details">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editModal<?php echo $user['user_id']; ?>"
                                                            title="Edit">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($user['status'] == 'active'): ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-warning" 
                                                            onclick="if(confirm('Are you sure you want to deactivate this user?')) { document.getElementById('deactivateForm<?php echo $user['user_id']; ?>').submit(); }"
                                                            title="Deactivate">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </button>
                                                    <?php else: ?>
                                                    <button type="button" 
                                                            class="btn btn-outline-success" 
                                                            onclick="if(confirm('Are you sure you want to activate this user?')) { document.getElementById('activateForm<?php echo $user['user_id']; ?>').submit(); }"
                                                            title="Activate">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <form id="deactivateForm<?php echo $user['user_id']; ?>" method="POST" style="display:none;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="status" value="inactive">
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                                <form id="activateForm<?php echo $user['user_id']; ?>" method="POST" style="display:none;">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                    <input type="hidden" name="status" value="active">
                                                    <input type="hidden" name="update_status" value="1">
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="modal fade" id="viewModal<?php echo $user['user_id']; ?>" tabindex="1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">User Details #<?php echo $user['user_id']; ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="text-center mb-4">
                                        <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px;">
                                            <span class="fs-2"><?php echo strtoupper(substr($user['full_name'], 0, 1)); ?></span>
                                        </div>
                                        <h5 class="mt-3"><?php echo escape($user['full_name']); ?></h5>
                                        <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'info'; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                        <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($user['status']); ?>
                                        </span>
                                    </div>
                                    <hr>
                                    <div class="mb-3">
                                        <strong><i class="bi bi-envelope"></i> Email:</strong><br>
                                        <?php echo escape($user['email']); ?>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="bi bi-telephone"></i> Phone:</strong><br>
                                        <?php echo escape($user['phone']); ?>
                                    </div>
                                    <?php if (isset($user['created_at'])): ?>
                                    <div class="mb-3">
                                        <strong><i class="bi bi-calendar-plus"></i> Registered:</strong><br>
                                        <?php echo formatDate($user['created_at']); ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (isset($user['last_login'])): ?>
                                    <div class="mb-3">
                                        <strong><i class="bi bi-clock-history"></i> Last Login:</strong><br>
                                        <?php echo $user['last_login'] ? formatDate($user['last_login']) : 'Never'; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal fade" id="editModal<?php echo $user['user_id']; ?>" tabindex="1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form method="POST" action="">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit User #<?php echo $user['user_id']; ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" class="form-control" name="full_name" 
                                                   value="<?php echo escape($user['full_name']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Email *</label>
                                            <input type="email" class="form-control" name="email" 
                                                   value="<?php echo escape($user['email']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Phone *</label>
                                            <input type="tel" class="form-control" name="phone" 
                                                   value="<?php echo escape($user['phone']); ?>" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Role</label>
                                            <select class="form-select" name="role" required>
                                                <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status" required>
                                                <option value="active" <?php echo $user['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $user['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label class="form-label">New Password</label>
                                            <input type="password" class="form-control" name="password" 
                                                   placeholder="Leave blank to keep current password">
                                            <small class="text-muted">Only fill if you want to change the password</small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                        <button type="submit" name="save_user" class="btn btn-primary">
                                            <i class="bi bi-check-circle"></i> Update User
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

<div class="modal fade" id="addUserModal" tabindex="1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" name="full_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone *</label>
                        <input type="tel" class="form-control" name="phone" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="user" selected>User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_user" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Create User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>