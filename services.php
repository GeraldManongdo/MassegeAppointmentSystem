<?php
require_once 'config/config.php';

$page_title = "Our Services";

// Get active services
$database = new Database();
$db = $database->getConnection();
$service_model = new Service($db);
$services = $service_model->getAll('active');

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Services Hero Section -->
 <section class="hero-section text-center text-white" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%), url('https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200') center/cover; padding: 120px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Our Massage Services</h1>
                <p class="lead mb-4">Experience the ultimate relaxation and rejuvenation at Senere Massage Parlor</p>
                <p class="mb-5"><i class="bi bi-geo-alt-fill"></i> Serving Quezon City, Philippines</p>
            </div>
        </div>
    </div>
</section>


<!-- Services Section -->
<section class="py-5">
    <div class="container">
        <?php if (!empty($services)): ?>
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="text-center mb-2">Choose Your Perfect Treatment</h2>
                    <p class="text-center text-muted">We offer a variety of professional massage therapies tailored to your needs</p>
                </div>
            </div>
            
            <div class="row g-4">
                <?php foreach ($services as $svc): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-sm service-card">
                            <?php if (!empty($svc['image_url'])): ?>
                                <img src="<?php echo escape($svc['image_url']); ?>" 
                                     class="card-img-top" 
                                     style="height: 250px; object-fit: cover;"
                                     alt="<?php echo escape($svc['service_name']); ?>">
                            <?php else: ?>
                                <div style="height: 250px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center;">
                                    <i class="bi bi-spa text-white" style="font-size: 4rem;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-3"><?php echo escape($svc['service_name']); ?></h5>
                                <p class="card-text text-muted flex-grow-1"><?php echo escape($svc['description']); ?></p>
                                
                                <div class="service-info mb-3">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">
                                            <i class="bi bi-clock text-primary"></i> Duration
                                        </span>
                                        <strong><?php echo $svc['duration']; ?> minutes</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">
                                            <i class="bi bi-tag text-primary"></i> Price
                                        </span>
                                        <strong class="text-primary fs-5">₱<?php echo number_format($svc['price'], 2); ?></strong>
                                    </div>
                                </div>
                                
                                <?php if (isLoggedIn()): ?>
                                    <a href="user/book.php?service=<?php echo $svc['service_id']; ?>" 
                                       class="btn btn-primary w-100">
                                        <i class="bi bi-calendar-plus"></i> Book Now
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-box-arrow-in-right"></i> Login to Book
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-inbox display-1 text-muted"></i>
                <p class="lead text-muted mt-3">No services available at the moment.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<?php if (!isLoggedIn()): ?>
<section class="bg-light py-5">
    <div class="container text-center">
        <h3 class="mb-4">Ready to Book Your Massage?</h3>
        <p class="lead text-muted mb-4">Create an account to start booking your appointments online</p>
        <div class="d-flex gap-3 justify-content-center">
            <a href="<?php echo SITE_URL; ?>register.php" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-person-plus"></i> Register Now
            </a>
            <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-outline-primary btn-lg px-5">
                <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
.service-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.page-header {
    position: relative;
    overflow: hidden;
}

.page-header::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,122.7C1248,107,1344,85,1392,74.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') bottom center no-repeat;
    background-size: cover;
}
</style>

<?php include 'includes/footer.php'; ?>
