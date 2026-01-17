<?php
$page_title = "Contact Us";
$hero_folder = "images/hero/contact/";
$hero_heading = "Get in Touch";
$hero_text = "We'd love to hear from you. Send us a message and we'll respond as soon as possible.";

include 'header.php';
require 'includes/config.php';
require 'includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Honeypot check (spam bots fill this hidden field)
    $honeypot = $_POST['website'] ?? '';
    
    // Time-based check (form should take at least 3 seconds to fill)
    $form_time = isset($_POST['form_time']) ? intval($_POST['form_time']) : 0;
    $current_time = time();
    $time_elapsed = $current_time - $form_time;
    
    // reCAPTCHA verification (if you add reCAPTCHA - see instructions below)
    $recaptcha_secret = ''; // Add your secret key here
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $recaptcha_valid = true;
    
    // If you enable reCAPTCHA, uncomment this:
    /*
    if (!empty($recaptcha_secret) && !empty($recaptcha_response)) {
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$recaptcha_secret}&response={$recaptcha_response}");
        $captcha_data = json_decode($verify);
        $recaptcha_valid = $captcha_data->success ?? false;
    }
    */
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Spam checks
    if (!empty($honeypot)) {
        // Honeypot was filled - likely a bot
        $error = "Invalid submission detected. Please try again.";
    } elseif ($time_elapsed < 3) {
        // Form submitted too quickly - likely a bot
        $error = "Form submitted too quickly. Please take your time.";
    } elseif (!$recaptcha_valid) {
        $error = "Please complete the reCAPTCHA verification.";
    } elseif (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($message) < 10) {
        $error = "Message must be at least 10 characters long.";
    } elseif (strlen($message) > 5000) {
        $error = "Message is too long. Please keep it under 5000 characters.";
    } else {
        // Additional spam detection - check for excessive links
        $link_count = substr_count(strtolower($message), 'http://') + substr_count(strtolower($message), 'https://');
        if ($link_count > 3) {
            $error = "Your message contains too many links. Please remove some and try again.";
        } else {
            // Save to DB
            try {
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, user_agent, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $name, 
                    $email, 
                    $phone,
                    $subject, 
                    $message,
                    $_SERVER['REMOTE_ADDR'] ?? '',
                    $_SERVER['HTTP_USER_AGENT'] ?? ''
                ]);
                
                // Send email notification
                $mailBody = "New Contact Form Submission\n\n";
                $mailBody .= "Name: $name\n";
                $mailBody .= "Email: $email\n";
                $mailBody .= "Phone: $phone\n";
                $mailBody .= "Subject: $subject\n\n";
                $mailBody .= "Message:\n$message\n\n";
                $mailBody .= "---\n";
                $mailBody .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
                $mailBody .= "Submitted: " . date('Y-m-d H:i:s');
                
                if (send_mail('admin@matatiele.co.za', "Contact Form: $subject", $mailBody)) {
                    $success = "Thank you for contacting us! We'll get back to you soon.";
                    // Clear form
                    $name = $email = $phone = $subject = $message = '';
                } else {
                    $success = "Your message has been received. We'll respond shortly.";
                }
            } catch (PDOException $e) {
                $error = "An error occurred. Please try again later.";
                error_log("Contact form error: " . $e->getMessage());
            }
        }
    }
}

// Pre-fill subject if passed via URL
$prefill_subject = $_GET['subject'] ?? '';
?>

<main class="contact-page">
    <div class="container">
        <div class="contact-wrapper">
            <!-- Contact Info Section -->
            <div class="contact-info">
                <h2>Contact Information</h2>
                <p class="intro-text">Have questions about visiting Matatiele? We're here to help! Reach out to us and we'll respond as quickly as possible.</p>
                
                <div class="info-cards">
                    <div class="info-card">
                        <div class="icon">📍</div>
                        <div class="info-content">
                            <h3>Visit Us</h3>
                            <p>Matatiele Tourism Office<br>
                            Main Street, Matatiele<br>
                            Eastern Cape, 4730</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="icon">📞</div>
                        <div class="info-content">
                            <h3>Call Us</h3>
                            <p><a href="tel:+27750923942">+27 75 092 3942</a><br>
                            Mon-Fri: 8:00 AM - 5:00 PM<br>
                            Sat: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="icon">✉️</div>
                        <div class="info-content">
                            <h3>Email Us</h3>
                            <p><a href="mailto:info@matatiele.co.za">info@matatiele.co.za</a><br>
                            We respond within 24 hours</p>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="icon">🌐</div>
                        <div class="info-content">
                            <h3>Follow Us</h3>
                            <div class="social-links">
                                <a href="#" title="Facebook">📘</a>
                                <a href="#" title="Twitter">🐦</a>
                                <a href="#" title="Instagram">📷</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="map-container">
                    <h3>Find Us</h3>
                    <!-- Replace with your actual Google Maps embed -->
                    <div class="map-placeholder">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3456.789!2d28.8!3d-30.3!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzDCsDE4JzAwLjAiUyAyOMKwNDgnMDAuMCJF!5e0!3m2!1sen!2sza!4v1234567890"
                            width="100%" 
                            height="300" 
                            style="border:0; border-radius: 12px;" 
                            allowfullscreen="" 
                            loading="lazy">
                        </iframe>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form Section -->
            <div class="contact-form-section">
                <h2>Send Us a Message</h2>
                
                <?php if($success): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <span><?php echo htmlspecialchars($success); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">✗</span>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
                <?php endif; ?>
                
                <form method="post" class="contact-form" id="contactForm">
                    <!-- Honeypot field (hidden from users, bots will fill it) -->
                    <input type="text" name="website" style="display:none !important;" tabindex="-1" autocomplete="off">
                    
                    <!-- Time-based check -->
                    <input type="hidden" name="form_time" value="<?php echo time(); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                required 
                                value="<?php echo htmlspecialchars($name ?? ''); ?>"
                                placeholder="John Doe"
                                minlength="2"
                                maxlength="100">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                                placeholder="john@example.com">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input 
                                type="tel" 
                                id="phone" 
                                name="phone" 
                                value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                                placeholder="+27 12 345 6789">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject *</label>
                            <input 
                                type="text" 
                                id="subject" 
                                name="subject" 
                                required 
                                value="<?php echo htmlspecialchars($subject ?? $prefill_subject); ?>"
                                placeholder="How can we help?"
                                minlength="3"
                                maxlength="200">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea 
                            id="message" 
                            name="message" 
                            required 
                            rows="6"
                            placeholder="Tell us more about your inquiry..."
                            minlength="10"
                            maxlength="5000"><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                        <div class="char-counter">
                            <span id="charCount">0</span> / 5000 characters
                        </div>
                    </div>
                    
                    <!-- Add reCAPTCHA here if you want (instructions below) -->
                    <!-- <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div> -->
                    
                    <div class="form-actions">
                        <button type="submit" class="submit-btn">
                            <span class="btn-text">Send Message</span>
                            <span class="btn-icon">→</span>
                        </button>
                        <p class="form-note">* Required fields. We respect your privacy and will never share your information.</p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<style>
.contact-page {
    padding: 3rem 0 5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.contact-wrapper {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: 3rem;
    max-width: 1400px;
    margin: 0 auto;
}

/* Contact Info Section */
.contact-info {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    height: fit-content;
}

.contact-info h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 1rem;
}

.intro-text {
    color: #666;
    line-height: 1.6;
    margin-bottom: 2rem;
}

.info-cards {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.info-card {
    display: flex;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border-radius: 12px;
    border-left: 4px solid #28a745;
    transition: transform 0.3s, box-shadow 0.3s;
}

.info-card:hover {
    transform: translateX(5px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.15);
}

.info-card .icon {
    font-size: 2rem;
    flex-shrink: 0;
}

.info-card h3 {
    margin: 0 0 0.5rem;
    color: #333;
    font-size: 1.1rem;
}

.info-card p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
    line-height: 1.6;
}

.info-card a {
    color: #28a745;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.3s;
}

.info-card a:hover {
    color: #20c997;
}

.social-links {
    display: flex;
    gap: 1rem;
    margin-top: 0.5rem;
}

.social-links a {
    font-size: 1.8rem;
    transition: transform 0.3s;
}

.social-links a:hover {
    transform: scale(1.2);
}

.map-container {
    margin-top: 2rem;
}

.map-container h3 {
    margin-bottom: 1rem;
    color: #333;
}

/* Contact Form Section */
.contact-form-section {
    background: white;
    padding: 2.5rem;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.contact-form-section h2 {
    font-size: 2rem;
    color: #333;
    margin-bottom: 1.5rem;
}

/* Alerts */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.8rem;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-icon {
    font-size: 1.5rem;
    font-weight: bold;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid #28a745;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid #dc3545;
}

/* Form Styles */
.contact-form {
    margin-top: 1.5rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #333;
    font-weight: 600;
    font-size: 0.95rem;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 0.9rem 1.2rem;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 1rem;
    font-family: inherit;
    transition: all 0.3s;
    background: #f8f9fa;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #28a745;
    background: white;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.char-counter {
    text-align: right;
    font-size: 0.85rem;
    color: #666;
    margin-top: 0.5rem;
}

/* Submit Button */
.form-actions {
    margin-top: 2rem;
}

.submit-btn {
    background: linear-gradient(45deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 1rem 2.5rem;
    border-radius: 30px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.8rem;
}

.submit-btn:hover {
    background: linear-gradient(45deg, #20c997, #28a745);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.submit-btn:active {
    transform: translateY(0);
}

.submit-btn .btn-icon {
    font-size: 1.3rem;
    transition: transform 0.3s;
}

.submit-btn:hover .btn-icon {
    transform: translateX(5px);
}

.form-note {
    margin-top: 1rem;
    font-size: 0.85rem;
    color: #666;
    font-style: italic;
}

/* Responsive */
@media (max-width: 968px) {
    .contact-wrapper {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .contact-page {
        padding: 2rem 0 3rem;
    }
    
    .contact-info,
    .contact-form-section {
        padding: 1.5rem;
    }
    
    .contact-info h2,
    .contact-form-section h2 {
        font-size: 1.5rem;
    }
    
    .submit-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Loading state */
.submit-btn.loading {
    opacity: 0.7;
    cursor: not-allowed;
    pointer-events: none;
}

.submit-btn.loading .btn-text::after {
    content: '';
    animation: dots 1.5s infinite;
}

@keyframes dots {
    0%, 20% { content: ''; }
    40% { content: '.'; }
    60% { content: '..'; }
    80%, 100% { content: '...'; }
}
</style>

<script>
// Character counter for message textarea
const messageField = document.getElementById('message');
const charCount = document.getElementById('charCount');

if (messageField && charCount) {
    messageField.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 4500) {
            charCount.style.color = '#dc3545';
        } else if (count > 4000) {
            charCount.style.color = '#ffc107';
        } else {
            charCount.style.color = '#666';
        }
    });
    
    // Initialize counter
    charCount.textContent = messageField.value.length;
}

// Form submission loading state
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function() {
        const submitBtn = this.querySelector('.submit-btn');
        submitBtn.classList.add('loading');
        submitBtn.querySelector('.btn-text').textContent = 'Sending';
    });
}

// Smooth scroll to form if there's an error or success message
window.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>

<!-- Optional: Add reCAPTCHA v2 -->
<!-- Step 1: Get keys from https://www.google.com/recaptcha/admin -->
<!-- Step 2: Add this script before </body> -->
<!-- <script src="https://www.google.com/recaptcha/api.js" async defer></script> -->

<?php include 'footer.php'; ?>