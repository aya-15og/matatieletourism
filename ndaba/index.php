<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dr LK Ndaba & Partners - General Practice | Queenstown</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Lato:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-content">
                <div class="logo">
                    <h1>Dr LK Ndaba</h1>
                </div>
                <ul class="nav-links">
                    <li><a href="#home" class="nav-link">Home</a></li>
                    <li><a href="#services" class="nav-link">Services</a></li>
                    <li><a href="#about" class="nav-link">About</a></li>
                    <li><a href="#contact" class="nav-link">Contact</a></li>
                </ul>
                <button class="btn-book" onclick="scrollToSection('appointment')">Book Now</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="hero-content">
                <h2>Caring for Queenstown's Health</h2>
                <p>Dr Lubabalo K. Ndaba and our dedicated team provide exceptional general practice services, focusing on personalized, compassionate care for every patient.</p>
                <div class="cta-buttons">
                    <a href="https://wa.me/27832716151?text=Hello%2C%20I%20would%20like%20to%20book%20an%20appointment%20with%20Dr%20LK%20Ndaba" target="_blank" class="btn btn-whatsapp">
                        <span>📱</span> WhatsApp Us
                    </a>
                    <button class="btn btn-primary" onclick="scrollToSection('appointment')">
                        <span>📅</span> Online Booking
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services">
        <div class="container">
            <div class="section-header">
                <h2>Our Services</h2>
                <div class="underline"></div>
            </div>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">❤️</div>
                    <h3>General Practice</h3>
                    <p>Comprehensive primary healthcare services for patients of all ages. From routine check-ups to acute illness management, we're here for your everyday healthcare needs.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">💊</div>
                    <h3>Clinical Dietitian</h3>
                    <p>Expert nutritional counseling and dietary planning to help you achieve optimal health through proper nutrition and lifestyle modifications.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">🏃</div>
                    <h3>Physiotherapy</h3>
                    <p>Professional physical therapy services to help restore movement, reduce pain, and improve your quality of life through evidence-based treatments.</p>
                </div>
                <div class="service-card">
                    <div class="service-icon">👥</div>
                    <h3>Diabetic Clinic</h3>
                    <p>Specialized diabetes management and education to help you effectively control your condition and prevent complications through comprehensive care.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Appointment Section -->
    <section id="appointment" class="appointment">
        <div class="container">
            <div class="section-header">
                <h2>Book an Appointment</h2>
                <div class="underline"></div>
            </div>
            <div class="form-wrapper">
                <div id="formMessage" class="form-message"></div>
                <form id="appointmentForm" method="POST" action="submit_appointment.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Your name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" required placeholder="+27 (0)45 838 5418">
                        </div>
                        <div class="form-group">
                            <label for="service">Service Required *</label>
                            <select id="service" name="service" required>
                                <option value="">Select a service</option>
                                <option value="general">General Practice</option>
                                <option value="dietitian">Clinical Dietitian</option>
                                <option value="physiotherapy">Physiotherapy</option>
                                <option value="diabetic">Diabetic Clinic</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="preferredDate">Preferred Date *</label>
                        <input type="date" id="preferredDate" name="preferredDate" required>
                    </div>

                    <div class="form-group">
                        <label for="message">Additional Information</label>
                        <textarea id="message" name="message" rows="4" placeholder="Please provide any additional information about your appointment..."></textarea>
                    </div>

                    <div class="captcha-box">
                        <p><strong>Security Check:</strong> <span id="captchaQuestion"></span></p>
                        <input type="number" id="captchaAnswer" name="captchaAnswer" required placeholder="Your answer">
                        <input type="hidden" id="captchaCorrect" name="captchaCorrect">
                    </div>

                    <button type="submit" class="btn btn-submit">Submit Appointment Request</button>
                </form>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="about">
        <div class="container">
            <div class="section-header">
                <h2>About Dr LK Ndaba</h2>
                <div class="underline"></div>
            </div>
            <div class="about-content">
                <div class="about-image">
                    <img src="images/about-doctor-bg.jpg" alt="Medical practice">
                </div>
                <div class="about-text">
                    <p>Dr Lubabalo K. Ndaba is a dedicated General Practitioner serving the Queenstown community with commitment and excellence in healthcare. With a patient-centered approach, Dr Ndaba and the professional team at the practice strive to provide comprehensive medical services tailored to each individual's needs.</p>
                    <p>Our practice combines modern medical expertise with compassionate care, ensuring that every patient receives the attention and treatment they deserve. We believe in building lasting relationships with our patients and their families.</p>
                    <button class="btn btn-primary" onclick="scrollToSection('appointment')">Schedule Your Visit</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact">
        <div class="container">
            <div class="section-header">
                <h2>Visit Us</h2>
                <div class="underline"></div>
            </div>
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">📍</div>
                    <h3>Location</h3>
                    <div class="contact-info">
                        <strong>Address:</strong><br>
                        32 Owen Street<br>
                        Queenstown Central<br>
                        Queenstown, 5319<br>
                        Eastern Cape, South Africa
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">📞</div>
                    <h3>Contact Details</h3>
                    <div class="contact-info">
                        <strong>Phone:</strong><br>
                        045 838 5418<br>
                        045 839 3788<br><br>
                        <strong>Mobile:</strong><br>
                        083 271 6151<br><br>
                        <strong>Email:</strong><br>
                        ndabalk@telkomsa.net
                    </div>
                </div>
                <div class="contact-card">
                    <div class="contact-icon">🕒</div>
                    <h3>Opening Hours</h3>
                    <table class="hours-table">
                        <tr>
                            <td>Monday - Friday</td>
                            <td><strong>08:00 - 18:00</strong></td>
                        </tr>
                        <tr>
                            <td>Saturday</td>
                            <td><strong>08:00 - 12:00</strong></td>
                        </tr>
                        <tr>
                            <td>Sunday</td>
                            <td><strong>Closed</strong></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Dr LK Ndaba & Partners. All rights reserved.</p>
            <p>Quality Healthcare for the Queenstown Community</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
