<?php
require_once 'config/config.php';

$page_title = "Home";

// Get active services
$database = new Database();
$db = $database->getConnection();
$service = new Service($db);
$services = $service->getAll('active');

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!-- Hero Section -->
<section class="hero-section text-center text-white" style="background: linear-gradient(135deg, rgba(102, 126, 234, 0.9) 0%, rgba(118, 75, 162, 0.9) 100%), url('https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=1200') center/cover; padding: 120px 0;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Welcome to Senere Massage Parlor</h1>
                <p class="lead mb-4">Experience ultimate relaxation and rejuvenation in the heart of Quezon City</p>
                <p class="mb-5"><i class="bi bi-geo-alt-fill"></i> Serving Quezon City, Philippines</p>
                <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                    <?php if (isLoggedIn()): ?>
                        <a href="<?php echo SITE_URL; ?>services.php" class="btn btn-light btn-lg px-5">
                            <i class="bi bi-calendar-plus"></i> Book Now
                        </a>
                        <a href="<?php echo SITE_URL; ?>user/appointments.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="bi bi-calendar-event"></i> My Appointments
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>services.php" class="btn btn-light btn-lg px-5 shadow">
                            <i class="bi bi-spa"></i> View Our Services
                        </a>
                        <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-outline-light btn-lg px-5">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <img src="https://images.unsplash.com/photo-1600334129128-685c5582fd35?w=600" 
                     class="img-fluid rounded shadow" 
                     alt="Massage Therapy">
            </div>
            <div class="col-lg-6">
                <h2 class="mb-4">About Senere Massage Parlor</h2>
                <p class="lead text-muted">Your trusted wellness partner in Quezon City</p>
                <p>At Senere Massage Parlor, we believe in the healing power of touch. Our team of certified and experienced massage therapists is dedicated to providing you with the highest quality massage services in a clean, comfortable, and relaxing environment.</p>
                <p>Located in the heart of Quezon City, we've been serving our community for years, helping our clients achieve better health, reduced stress, and improved well-being through professional massage therapy.</p>
                <ul class="list-unstyled mt-4">
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Licensed & Certified Therapists</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Clean & Sanitized Facilities</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Flexible Scheduling</li>
                    <li class="mb-2"><i class="bi bi-check-circle-fill text-primary me-2"></i> Affordable Prices</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">Our Massage Services</h2>
            <p class="text-muted">Choose from our wide range of professional massage therapies</p>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($services)): ?>
                <?php foreach (array_slice($services, 0, 3) as $svc): ?>
                    <div class="col-md-4">
                        <div class="card service-card h-100 shadow-sm">
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
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo escape(substr($svc['description'], 0, 100)); ?><?php echo strlen($svc['description']) > 100 ? '...' : ''; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                                    <span class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo $svc['duration']; ?> mins
                                    </span>
                                    <span class="h5 text-primary mb-0">₱<?php echo number_format($svc['price'], 2); ?></span>
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
            <?php else: ?>
                <div class="col-12 text-center">
                    <p class="text-muted">No services available at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-5">
            <a href="<?php echo SITE_URL; ?>services.php" class="btn btn-primary btn-lg px-5">
                <i class="bi bi-grid"></i> View All Services
            </a>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">Benefits of Massage Therapy</h2>
            <p class="text-muted">Why regular massage is essential for your health and well-being</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-heart-pulse"></i>
                    </div>
                    <h5>Stress Relief</h5>
                    <p class="text-muted">Reduce stress and anxiety through relaxing massage techniques that calm your mind and body.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-bandaid"></i>
                    </div>
                    <h5>Pain Management</h5>
                    <p class="text-muted">Alleviate muscle tension, back pain, and chronic pain with therapeutic massage treatments.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-activity"></i>
                    </div>
                    <h5>Better Circulation</h5>
                    <p class="text-muted">Improve blood flow and oxygen delivery throughout your body for better overall health.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-moon-stars"></i>
                    </div>
                    <h5>Improved Sleep</h5>
                    <p class="text-muted">Experience deeper, more restful sleep after regular massage therapy sessions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Boost Immunity</h5>
                    <p class="text-muted">Strengthen your immune system and enhance your body's natural healing abilities.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="text-center p-4">
                    <div class="display-4 text-primary mb-3">
                        <i class="bi bi-person-arms-up"></i>
                    </div>
                    <h5>Flexibility</h5>
                    <p class="text-muted">Increase range of motion and flexibility with targeted massage techniques.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">Why Choose Senere Massage Parlor?</h2>
            <p class="text-muted">Experience the difference with our professional services</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h5 class="card-title">Easy Online Booking</h5>
                        <p class="card-text text-muted">Book your appointments 24/7 through our convenient online system.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-award"></i>
                        </div>
                        <h5 class="card-title">Expert Therapists</h5>
                        <p class="card-text text-muted">All our therapists are licensed, certified, and highly experienced.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-house-heart"></i>
                        </div>
                        <h5 class="card-title">Relaxing Ambiance</h5>
                        <p class="card-text text-muted">Enjoy our clean, comfortable, and peaceful environment.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3">
                            <i class="bi bi-tag"></i>
                        </div>
                        <h5 class="card-title">Affordable Rates</h5>
                        <p class="card-text text-muted">Premium quality massage services at competitive prices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="mb-3">How to Book Your Massage</h2>
            <p class="text-muted">Simple steps to schedule your relaxation session</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-3 text-center">
                <div class="step-circle mx-auto mb-3">1</div>
                <h5>Create Account</h5>
                <p class="text-muted">Sign up with your email and basic information</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="step-circle mx-auto mb-3">2</div>
                <h5>Choose Service</h5>
                <p class="text-muted">Select from our variety of massage treatments</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="step-circle mx-auto mb-3">3</div>
                <h5>Pick Date & Time</h5>
                <p class="text-muted">Choose available slot that fits your schedule</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="step-circle mx-auto mb-3">4</div>
                <h5>Get Confirmed</h5>
                <p class="text-muted">Receive instant confirmation and enjoy your massage</p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="py-5 text-white text-center" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
    <div class="container">
        <h2 class="mb-4">Ready to Experience Ultimate Relaxation?</h2>
        <p class="lead mb-4">Book your massage appointment today and let our expert therapists take care of you</p>
        <?php if (!isLoggedIn()): ?>
            <div class="d-grid gap-3 d-md-flex justify-content-md-center">
                <a href="<?php echo SITE_URL; ?>register.php" class="btn btn-light btn-lg px-5">
                    <i class="bi bi-person-plus"></i> Register Now
                </a>
                <a href="<?php echo SITE_URL; ?>services.php" class="btn btn-outline-light btn-lg px-5">
                    <i class="bi bi-grid"></i> Browse Services
                </a>
            </div>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>user/services.php" class="btn btn-light btn-lg px-5">
                <i class="bi bi-calendar-plus"></i> Book Appointment Now
            </a>
        <?php endif; ?>
    </div>
</section>

<!-- Contact/Location Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="mb-4">Visit Us</h2>
                <div class="mb-3">
                    <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                    <strong>Location:</strong> Quezon City, Philippines
                </div>
                <div class="mb-3">
                    <i class="bi bi-telephone-fill text-primary me-2"></i>
                    <strong>Phone:</strong> (02) 1234-5678
                </div>
                <div class="mb-3">
                    <i class="bi bi-envelope-fill text-primary me-2"></i>
                    <strong>Email:</strong> info@senere-massage.com
                </div>
                <div class="mb-3">
                    <i class="bi bi-clock-fill text-primary me-2"></i>
                    <strong>Hours:</strong> Monday - Sunday, 9:00 AM - 9:00 PM
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h5 class="mb-3">Have Questions?</h5>
                        <p class="text-muted mb-4">Feel free to contact us or book your appointment online!</p>
                        <a href="<?php echo SITE_URL; ?>login.php" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-calendar-plus"></i> Book Online Now
                        </a>
                        <a href="tel:0212345678" class="btn btn-outline-primary w-100">
                            <i class="bi bi-telephone"></i> Call Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.service-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}

.step-circle {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: bold;
}

.hero-section {
    position: relative;
}

.hero-section::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,122.7C1248,107,1344,85,1392,74.7L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') bottom center no-repeat;
    background-size: cover;
    pointer-events: none;
    z-index: 0;
}

.hero-section .container {
    position: relative;
    z-index: 1;
}
</style>

<!-- Footer -->
<footer class="footer bg-dark text-white py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5><i class="bi bi-spa text-primary"></i> <?php echo SITE_NAME; ?></h5>
                <p class="text-light">Your trusted wellness partner in Quezon City. Experience ultimate relaxation and rejuvenation.</p>
                <p class="mb-0"><i class="bi bi-geo-alt-fill text-primary"></i> <?php echo SITE_LOCATION; ?></p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h6>Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>index.php" class="text-light text-decoration-none">
                        <i class="bi bi-house me-2"></i>Home
                    </a></li>
                    <li class="mb-2"><a href="<?php echo SITE_URL; ?>services.php" class="text-light text-decoration-none">
                        <i class="bi bi-grid me-2"></i>Services
                    </a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Contact Us</h6>
                <p class="text-light mb-2">
                    <i class="bi bi-telephone-fill text-primary me-2"></i>(02) 1234-5678
                </p>
                <p class="text-light mb-2">
                    <i class="bi bi-envelope-fill text-primary me-2"></i>info@senere-massage.com
                </p>
                <p class="text-light mb-2">
                    <i class="bi bi-clock-fill text-primary me-2"></i>Mon-Sun: 9:00 AM - 9:00 PM
                </p>
            </div>
        </div>
        <hr class="bg-secondary my-4">
        <div class="text-center">
            <small class="text-light">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</small>
        </div>
    </div>
</footer>

<?php include 'includes/footer.php'; ?>
