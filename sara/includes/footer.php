<!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Sara-Lee Guesthouse</h3>
                    <p>Experience warm hospitality and comfortable accommodation in a beautiful setting.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook">📘</a>
                        <a href="#" aria-label="Instagram">📷</a>
                        <a href="#" aria-label="TripAdvisor">✈️</a>
                    </div>
                </div>

                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="rooms.php">Our Rooms</a></li>
                        <li><a href="gallery.php">Gallery</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="booking.php">Book Now</a></li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <ul class="footer-contact">
                        <li>
                            <strong>Phone:</strong><br>
                            <a href="tel:0798911983">079 891 1983</a>
                        </li>
                        <li>
                            <strong>Email:</strong><br>
                            <a href="mailto:bookings@sara-lee.co.za">bookings@sara-lee.co.za</a>
                        </li>
                        <li>
                            <strong>Location:</strong><br>
                            6 Seymour Street, Matatiele<br>
                            South Africa
                        </li>
                    </ul>
                </div>

                <div class="footer-section">
                    <h3>Business Hours</h3>
                    <ul class="footer-hours">
                        <li><strong>Check-in:</strong> 2:00 PM - 7:00 PM</li>
                        <li><strong>Check-out:</strong> 10:00 AM</li>
                        <li><strong>Breakfast:</strong> 7:00 AM - 9:30 AM</li>
                        <li><strong>Reception:</strong> 24 Hours</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Sara-Lee Guesthouse. All rights reserved.</p>
                <p class="footer-links-bottom">
                    <a href="privacy.php">Privacy Policy</a> | 
                    <a href="terms.php">Terms & Conditions</a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top" aria-label="Back to top">↑</button>

    <script>
        // Back to top button
        const backToTopBtn = document.getElementById('backToTop');
        
        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });

        backToTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>