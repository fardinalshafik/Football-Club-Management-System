<footer class="footer mt-5">
    <div class="container">
        <div class="footer-content">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-light mb-3">
                        <i class="fas fa-futbol me-2"></i>Football Club Manager
                    </h5>
                    <p class="text-light-50 mb-0">
                        Professional football club management system for modern teams.
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="footer-links">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i>Dashboard
                        </a>
                        <a href="players.php">
                            <i class="fas fa-users me-1"></i>Players
                        </a>
                        <a href="matches.php">
                            <i class="fas fa-calendar-alt me-1"></i>Matches
                        </a>
                        <a href="about.php">
                            <i class="fas fa-info-circle me-1"></i>About
                        </a>
                    </div>
                    
                    <div class="social-links mt-3">
                        <a href="#" class="text-light me-3" title="Facebook">
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="text-light me-3" title="Twitter">
                            <i class="fab fa-twitter fa-lg"></i>
                        </a>
                        <a href="#" class="text-light me-3" title="Instagram">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-light" title="LinkedIn">
                            <i class="fab fa-linkedin fa-lg"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4" style="border-color: rgba(255,255,255,0.2);">
            
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="text-light-50 mb-0">
                        &copy; <?php echo date("Y"); ?> Football Club Management System. All rights reserved.
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <small class="text-light-50">
                        Version 1.0 | Built with <i class="fas fa-heart text-danger"></i>
                    </small>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Back to top button -->
<button id="backToTop" class="btn btn-primary position-fixed" style="bottom: 20px; right: 20px; z-index: 1000; display: none; border-radius: 50%; width: 50px; height: 50px;">
    <i class="fas fa-arrow-up"></i>
</button>

<script>
// Back to top functionality
window.addEventListener('scroll', function() {
    const backToTopBtn = document.getElementById('backToTop');
    if (window.pageYOffset > 300) {
        backToTopBtn.style.display = 'block';
    } else {
        backToTopBtn.style.display = 'none';
    }
});

document.getElementById('backToTop').addEventListener('click', function() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});

// Add fade-in animation to main content
document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.container');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
});
</script>