<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="d-flex flex-column flex-shrink-0 p-3 bg-dark sidebar" style="width: 280px;">
    <a href="<?php echo SITE_URL; ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="bi bi-spa fs-4 me-2 text-primary"></i>
        <span class="fs-5 fw-bold">
            <span class="text-primary"><?php echo SITE_NAME; ?></span>
            <small class="d-block text-muted" style="font-size: 0.7rem; font-weight: 300;">Admin Panel</small>
        </span>
    </a>
    <hr class="text-white-50">
    
    <!-- Navigation Links -->
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="<?php echo SITE_URL; ?>admin/dashboard.php" 
               class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : 'text-white'; ?>">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo SITE_URL; ?>admin/bookings.php" 
               class="nav-link <?php echo $current_page == 'bookings.php' ? 'active' : 'text-white'; ?>">
                <i class="bi bi-calendar3"></i>
                Bookings
            </a>
        </li>
        <li>
            <a href="<?php echo SITE_URL; ?>admin/services.php" 
               class="nav-link <?php echo $current_page == 'services.php' ? 'active' : 'text-white'; ?>">
                <i class="bi bi-grid"></i>
                Services
            </a>
        </li>
        <li>
            <a href="<?php echo SITE_URL; ?>admin/users.php" 
               class="nav-link <?php echo $current_page == 'users.php' ? 'active' : 'text-white'; ?>">
                <i class="bi bi-people"></i>
                Users
            </a>
        </li>
    </ul>
    
    <hr class="text-white-50">
    
    <!-- User Dropdown -->
    <div class="dropup">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle p-2 rounded hover-bg-secondary" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
            <div class="avatar-circle bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                <span class="fw-bold"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></span>
            </div>
            <div class="flex-grow-1 h-100 d-flex align-items-center">
                <div class="fw-semibold" style="font-size: 0.95rem;"><?php echo escape($_SESSION['full_name']); ?></div>
                <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
            </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark shadow w-100" aria-labelledby="dropdownUser">
            <li>
                <a class="dropdown-item" href="<?php echo SITE_URL; ?>index.php" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-2"></i> View Website
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>logout.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<style>
.hover-bg-secondary:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    transition: background-color 0.2s ease;
}

.dropdown-menu-dark {
    background-color: #2d3238;
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.dropdown-menu-dark .dropdown-item {
    color: #fff;
    transition: all 0.2s ease;
}

.dropdown-menu-dark .dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1);
    padding-left: 1.2rem;
}

.dropdown-menu-dark .dropdown-item.text-danger:hover {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545 !important;
}

.dropdown-menu-dark .dropdown-divider {
    border-color: rgba(255, 255, 255, 0.1);
}
</style>
