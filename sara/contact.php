<?php
session_start();
$page = 'contact';

// Form handling
$message_sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    // If no errors, send email
    if (empty($errors)) {
        $to = "bookings@sara-lee.co.za";
        $email_subject = "Contact Form: " . $subject;
        $email_body = "Name: $name\n";
        $email_body .= "Email: $email\n";
        $email_body .= "Phone: $phone\n\n";
        $email_body .= "Message:\n$message";
        
        $headers = "From: $email\r\n";
        $headers .= "Reply-To: $email\r\n";
        
        if (mail($to, $email_subject, $email_body, $headers)) {
            $message_sent = true;
            // Clear form
            $_POST = [];
        } else {
            $errors[] = "Failed to send message. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>Contact Us</h1>
        <p>We'd love to hear from you</p>
    </div>
</section>

<!-- Contact Section -->
<section class="contact-section">
    <div class="container">
        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info-box">
                <h3>Get In Touch</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h4>Phone</h4>
                        <p><a href="tel:0798911983">079 891 1983</a></p>
                        <p>Mon - Sun: 7:00 AM - 9:00 PM</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-details">
                        <h4>Email</h4>
                        <p><a href="mailto:bookings@sara-lee.co.za">bookings@sara-lee.co.za</a></p>
                        <p><a href="mailto:info@sara-lee.co.za">info@sara-lee.co.za</a></p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-details">
                        <h4>Location</h4>
                        <p>6 Seymour Street, Matatiele, 4730<br>
                        South Africa</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-details">
                        <h4>Business Hours</h4>
                        <p><strong>Reception:</strong> 24 Hours<br>
                        <strong>Check-in:</strong> 2:00 PM - 7:00 PM<br>
                        <strong>Check-out:</strong> 10:00 AM<br>
                        <strong>Breakfast:</strong> 7:00 AM - 9:30 AM</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">🌐</div>
                    <div class="contact-details">
                        <h4>Website</h4>
                        <p><a href="https://www.sara-lee.co.za" target="_blank">www.sara-lee.co.za</a></p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <h3>Send Us A Message</h3>
                
                <?php if ($message_sent): ?>
                    <div class="form-alert success">
                        <strong>Success!</strong> Your message has been sent. We'll get back to you soon!
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="form-alert error">
                        <strong>Error!</strong>
                        <ul style="margin: 10px 0 0 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" 
                               placeholder="e.g., Booking Inquiry">
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block">Send Message</button>
                </form>
            </div>
        </div>

        <!-- Map -->
       <!-- <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3310.123456789!2d18.123456!3d-34.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzTCsDA3JzI0LjQiUyAxOMKwMDcnMjQuNCJF!5e0!3m2!1sen!2sza!4v1234567890" 
                    allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>-->
    </div>
</section>

<!-- FAQ Section -->
<section class="faq-section" style="background: var(--bg-light); padding: 60px 0;">
    <div class="container">
        <h2 class="section-title">Frequently Asked Questions</h2>
        <div class="faq-grid" style="max-width: 900px; margin: 40px auto;">
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">What time is check-in and check-out?</h4>
                <p style="color: var(--text-light); margin: 0;">Check-in is from 2:00 PM to 7:00 PM. Check-out is at 10:00 AM. Late check-in can be arranged by prior notice.</p>
            </div>
            
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Is breakfast included?</h4>
                <p style="color: var(--text-light); margin: 0;">Yes! A delicious homemade breakfast is included with all our room rates. Breakfast is served from 7:00 AM to 9:30 AM.</p>
            </div>
            
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Do you have parking available?</h4>
                <p style="color: var(--text-light); margin: 0;">Yes, we provide secure off-street parking for all our guests free of charge.</p>
            </div>
            
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">What is your cancellation policy?</h4>
                <p style="color: var(--text-light); margin: 0;">Free cancellation up to 48 hours before arrival. Cancellations within 48 hours will be charged for the first night.</p>
            </div>
            
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Do you allow pets?</h4>
                <p style="color: var(--text-light); margin: 0;">Unfortunately, we do not allow pets at our guesthouse. We apologize for any inconvenience.</p>
            </div>
            
            <div class="faq-item" style="background: var(--white); padding: 25px; border-radius: 10px; margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="color: var(--primary-color); margin-bottom: 10px;">Is Wi-Fi available?</h4>
                <p style="color: var(--text-light); margin: 0;">Yes, high-speed Wi-Fi is available throughout the property at no extra charge.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>