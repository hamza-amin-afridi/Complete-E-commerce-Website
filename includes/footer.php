    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">
                        <i class="fas fa-shopping-bag text-primary me-2"></i>E-Shop
                    </h5>
                    <p class="text-muted">
                        Your one-stop destination for premium quality products. We offer the best selection of electronics, fashion, home goods, and more at competitive prices.
                    </p>
                    <div class="mt-4">
                        <a href="#" class="btn btn-outline-light btn-floating me-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating me-2">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating me-2">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="btn btn-outline-light btn-floating me-2">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="shop.php" class="text-muted text-decoration-none">Shop</a></li>
                        <li class="mb-2"><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <!-- Customer Service -->
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Customer Service</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">FAQ</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Shipping Info</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Returns</a></li>
                        <li class="mb-2"><a href="#" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="text-uppercase mb-4">Contact Us</h5>
                    <ul class="list-unstyled text-muted">
                        <li class="mb-3">
                            <i class="fas fa-home me-3"></i>
                            123 Commerce Street, New York, NY 10001
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-envelope me-3"></i>
                            support@eshop.com
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-phone me-3"></i>
                            +1 (555) 123-4567
                        </li>
                    </ul>
                    
                    <!-- Newsletter -->
                    <h6 class="text-uppercase mb-3">Subscribe to our Newsletter</h6>
                    <form id="newsletterForm" class="input-group">
                        <input type="email" name="email" id="newsletterEmail" class="form-control" placeholder="Your email" aria-label="Your email" required>
                        <button class="btn btn-primary" type="submit" id="newsletterBtn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                    <div id="newsletterMessage" class="mt-2 small"></div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; <?php echo date('Y'); ?> E-Shop. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="https://via.placeholder.com/50x30/fff/000?text=VISA" alt="Visa" class="me-2" style="height: 30px;">
                    <img src="https://via.placeholder.com/50x30/fff/000?text=MC" alt="Mastercard" class="me-2" style="height: 30px;">
                    <img src="https://via.placeholder.com/50x30/fff/000?text=AMEX" alt="Amex" class="me-2" style="height: 30px;">
                    <img src="https://via.placeholder.com/50x30/fff/000?text=PP" alt="PayPal" style="height: 30px;">
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Back to Top Button -->
    <button type="button" class="btn btn-primary btn-lg btn-floating" id="btn-back-to-top" style="position: fixed; bottom: 20px; right: 20px; display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- MDBootstrap JS -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.0/mdb.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="/E-Commerce%20Website/assets/js/main.js"></script>
    
    <script>
        // Back to top button functionality
        let mybutton = document.getElementById("btn-back-to-top");
        
        window.onscroll = function () {
            scrollFunction();
        };
        
        function scrollFunction() {
            if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
                mybutton.style.display = "block";
            } else {
                mybutton.style.display = "none";
            }
        }
        
        mybutton.addEventListener("click", function() {
            document.body.scrollTop = 0;
            document.documentElement.scrollTop = 0;
        });
        
        // Newsletter Form Submission
        $(document).ready(function() {
            $('#newsletterForm').on('submit', function(e) {
                e.preventDefault();
                const email = $('#newsletterEmail').val();
                const $btn = $('#newsletterBtn');
                const $msg = $('#newsletterMessage');
                
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
                
                $.ajax({
                    url: 'newsletter.php',
                    type: 'POST',
                    data: { email: email },
                    dataType: 'json',
                    success: function(response) {
                        $msg.html('<span class="' + (response.success ? 'text-success' : 'text-warning') + '">' + response.message + '</span>');
                        if (response.success) {
                            $('#newsletterEmail').val('');
                        }
                    },
                    error: function() {
                        $msg.html('<span class="text-danger">Error subscribing. Please try again.</span>');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i>');
                    }
                });
            });
        });
    </script>
</body>
</html>
