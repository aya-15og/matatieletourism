<?php
session_start();
$page = 'privacy';
include 'includes/header.php';
?>

<!-- Page Header -->
<section class="page-header">
    <div class="page-header-overlay"></div>
    <div class="page-header-content">
        <h1>Privacy Policy</h1>
        <p>Your privacy matters to us</p>
    </div>
</section>

<!-- Privacy Content -->
<section style="padding: 60px 0;">
    <div class="container" style="max-width: 900px;">
        <div style="background: var(--white); padding: 40px; border-radius: 15px; box-shadow: var(--shadow);">
            <p style="color: var(--text-light); margin-bottom: 30px;">
                <strong>Last Updated:</strong> <?php echo date('F d, Y'); ?>
            </p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">1. Information We Collect</h2>
            <p>When you make a booking or contact us, we may collect the following information:</p>
            <ul style="margin-left: 20px; color: var(--text-light);">
                <li>Full name</li>
                <li>Email address</li>
                <li>Phone number</li>
                <li>Address (if required)</li>
                <li>Payment information</li>
                <li>Special requests or preferences</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 30px;">2. How We Use Your Information</h2>
            <p>We use your personal information to:</p>
            <ul style="margin-left: 20px; color: var(--text-light);">
                <li>Process and confirm your booking</li>
                <li>Communicate with you about your stay</li>
                <li>Provide customer service and support</li>
                <li>Send booking confirmations and receipts</li>
                <li>Improve our services</li>
                <li>Comply with legal requirements</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 30px;">3. Information Sharing</h2>
            <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>
            <ul style="margin-left: 20px; color: var(--text-light);">
                <li>With your explicit consent</li>
                <li>To comply with legal obligations</li>
                <li>To protect our rights and property</li>
                <li>With service providers who help us operate our business (e.g., payment processors)</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 30px;">4. Data Security</h2>
            <p>We implement appropriate security measures to protect your personal information from unauthorized access, alteration, disclosure, or destruction. However, no method of transmission over the internet is 100% secure.</p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">5. Cookies</h2>
            <p>Our website may use cookies to enhance your browsing experience. You can choose to disable cookies through your browser settings, though this may affect website functionality.</p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">6. Your Rights</h2>
            <p>You have the right to:</p>
            <ul style="margin-left: 20px; color: var(--text-light);">
                <li>Access your personal information</li>
                <li>Request correction of inaccurate data</li>
                <li>Request deletion of your data</li>
                <li>Opt-out of marketing communications</li>
                <li>Lodge a complaint with relevant authorities</li>
            </ul>

            <h2 style="color: var(--primary-color); margin-top: 30px;">7. Data Retention</h2>
            <p>We retain your personal information only for as long as necessary to fulfill the purposes outlined in this privacy policy, comply with legal obligations, and resolve disputes.</p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">8. Children's Privacy</h2>
            <p>Our services are not directed to individuals under 18 years of age. We do not knowingly collect personal information from children.</p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">9. Changes to This Policy</h2>
            <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last Updated" date.</p>

            <h2 style="color: var(--primary-color); margin-top: 30px;">10. Contact Us</h2>
            <p>If you have any questions about this privacy policy or wish to exercise your rights, please contact us:</p>
            <div style="background: var(--bg-light); padding: 20px; border-radius: 10px; margin-top: 20px;">
                <p style="margin: 5px 0;"><strong>Sara-Lee Guesthouse</strong></p>
                <p style="margin: 5px 0;">Email: <a href="mailto:bookings@sara-lee.co.za" style="color: var(--primary-color);">bookings@sara-lee.co.za</a></p>
                <p style="margin: 5px 0;">Phone: <a href="tel:0798911983" style="color: var(--primary-color);">079 891 1983</a></p>
                <p style="margin: 5px 0;">Address: Hermanus Road, South Africa</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>