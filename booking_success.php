<?php
session_start();
$page_title = "Booking Confirmation";
include 'header.php';

$booking_ref = $_GET['ref'] ?? '';
$success_data = $_SESSION['booking_success'] ?? null;

if (!$success_data || !$booking_ref) {
    header("Location: stays.php");
    exit;
}

// Clear session data
unset($_SESSION['booking_success']);
?>

<main class="success-main">
    <div class="container">
        <div class="success-container">
            <?php if ($success_data['type'] === 'partner'): ?>
                <!-- Partner Booking Success -->
                <div class="success-card">
                    <div class="success-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    
                    <h1>🎉 Booking Confirmed!</h1>
                    <p class="success-subtitle">Your reservation has been successfully confirmed</p>
                    
                    <div class="reference-box">
                        <span class="reference-label">Booking Reference</span>
                        <span class="reference-number"><?= htmlspecialchars($booking_ref) ?></span>
                        <button onclick="copyReference()" class="copy-btn" id="copyBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy
                        </button>
                    </div>
                    
                    <div class="info-card">
                        <h3>✅ What's Next?</h3>
                        <ul class="next-steps">
                            <li>
                                <strong>Check Your Email</strong>
                                <p>We've sent a confirmation email with all your booking details</p>
                            </li>
                            <li>
                                <strong>Payment Instructions</strong>
                                <p>You will receive payment details shortly from the property</p>
                            </li>
                            <li>
                                <strong>Property Contact</strong>
                                <p>The accommodation has been notified and will contact you if needed</p>
                            </li>
                            <li>
                                <strong>Save Your Reference</strong>
                                <p>Keep your booking reference number for any future correspondence</p>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="important-note">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <strong>Important:</strong> Please complete payment as per the instructions you'll receive to secure your booking.
                        </div>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- Non-Partner Inquiry Success -->
                <div class="success-card inquiry">
                    <div class="success-icon inquiry-icon">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    
                    <h1>📨 Inquiry Sent Successfully!</h1>
                    <p class="success-subtitle">Your booking inquiry has been forwarded to the property</p>
                    
                    <div class="reference-box inquiry-ref">
                        <span class="reference-label">Inquiry Reference</span>
                        <span class="reference-number"><?= htmlspecialchars($booking_ref) ?></span>
                        <button onclick="copyReference()" class="copy-btn" id="copyBtn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy
                        </button>
                    </div>
                    
                    <div class="info-card warning">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <div>
                            <h3>⚠️ Please Note</h3>
                            <p><strong><?= htmlspecialchars($success_data['stay_name']) ?></strong> is not a partner property. This means:</p>
                            <ul>
                                <li>Your inquiry has been sent directly to the property via email</li>
                                <li>Confirmation depends on their availability and response</li>
                                <li>Response time may vary (typically within 24-48 hours)</li>
                                <li>There are no guarantees until you receive direct confirmation</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <h3>📞 What's Next?</h3>
                        <ul class="next-steps">
                            <li>
                                <strong>Wait for Property Response</strong>
                                <p>The property will review your inquiry and contact you directly</p>
                            </li>
                            <li>
                                <strong>Check Your Email</strong>
                                <p>We've sent you a confirmation email with the inquiry details</p>
                            </li>
                            <li>
                                <strong>Contact Directly (Optional)</strong>
                                <p>You can also reach out to the property directly using the contact information provided in your email</p>
                            </li>
                            <li>
                                <strong>No Response?</strong>
                                <p>If you don't hear back within 48 hours, we recommend contacting them directly</p>
                            </li>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="stays.php" class="btn btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Browse More Accommodations
                </a>
                <a href="index.php" class="btn btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                    Return to Homepage
                </a>
            </div>
            
            <!-- Support Section -->
            <div class="support-section">
                <h3>Need Help?</h3>
                <p>If you have any questions or need assistance, please don't hesitate to contact us:</p>
                <div class="support-contacts">
                    <a href="mailto:bookings@matatiele.co.za" class="support-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                        bookings@matatiele.co.za
                    </a>
                    <a href="tel:+27123456789" class="support-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                        </svg>
                        +27 (0)12 345 6789
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
:root {
    --success: #10b981;
    --warning: #f59e0b;
    --primary: #2563eb;
    --text-dark: #1f2937;
    --text-medium: #6b7280;
    --border: #e5e7eb;
    --bg-light: #f9fafb;
}

.success-main {
    padding: 4rem 0;
    background: linear-gradient(to bottom, var(--bg-light), white);
    min-height: calc(100vh - 200px);
}

.success-container {
    max-width: 700px;
    margin: 0 auto;
}

.success-card {
    background: white;
    border-radius: 20px;
    padding: 3rem;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    margin-bottom: 2rem;
}

.success-card.inquiry {
    border-top: 4px solid var(--warning);
}

.success-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 120px;
    height: 120px;
    background: linear-gradient(135deg, #d1fae5, #a7f3d0);
    border-radius: 50%;
    margin-bottom: 2rem;
}

.success-icon svg {
    color: var(--success);
}

.inquiry-icon {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
}

.inquiry-icon svg {
    color: var(--warning);
}

.success-card h1 {
    font-size: 2.25rem;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.success-subtitle {
    font-size: 1.125rem;
    color: var(--text-medium);
    margin-bottom: 2rem;
}

.reference-box {
    background: var(--bg-light);
    padding: 1.5rem;
    border-radius: 12px;
    margin: 2rem 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    border: 2px solid var(--success);
}

.inquiry-ref {
    border-color: var(--warning);
}

.reference-label {
    font-size: 0.875rem;
    color: var(--text-medium);
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.reference-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text-dark);
    font-family: 'Courier New', monospace;
}

.copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: white;
    border: 1px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
    font-weight: 500;
    color: var(--text-medium);
    transition: all 0.2s;
}

.copy-btn:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.info-card {
    background: #eff6ff;
    border-left: 4px solid var(--primary);
    padding: 1.5rem;
    border-radius: 8px;
    text-align: left;
    margin: 2rem 0;
}

.info-card.warning {
    background: #fef3c7;
    border-left-color: var(--warning);
    display: flex;
    gap: 1rem;
}

.info-card h3 {
    font-size: 1.25rem;
    color: var(--text-dark);
    margin-bottom: 1rem;
}

.next-steps {
    list-style: none;
    padding: 0;
}

.next-steps li {
    padding: 1rem 0;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.next-steps li:last-child {
    border-bottom: none;
}

.next-steps strong {
    display: block;
    color: var(--text-dark);
    margin-bottom: 0.25rem;
}

.next-steps p {
    color: var(--text-medium);
    margin: 0;
}

.important-note {
    background: #fef3c7;
    padding: 1rem 1.5rem;
    border-radius: 8px;
    display: flex;
    gap: 1rem;
    align-items: start;
    text-align: left;
    margin-top: 2rem;
}

.important-note svg {
    flex-shrink: 0;
    color: var(--warning);
}

.action-buttons {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary), #0ea5e9);
    color: white;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
}

.btn-secondary {
    background: white;
    color: var(--primary);
    border: 2px solid var(--primary);
}

.btn-secondary:hover {
    background: var(--primary);
    color: white;
}

.support-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.support-section h3 {
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-bottom: 0.5rem;
}

.support-section p {
    color: var(--text-medium);
    margin-bottom: 1.5rem;
}

.support-contacts {
    display: flex;
    justify-content: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.support-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--primary);
    text-decoration: none;
    font-weight: 500;
    padding: 0.75rem 1.5rem;
    background: var(--bg-light);
    border-radius: 8px;
    transition: all 0.2s;
}

.support-link:hover {
    background: var(--primary);
    color: white;
}

@media (max-width: 768px) {
    .success-card {
        padding: 2rem 1.5rem;
    }
    
    .success-card h1 {
        font-size: 1.75rem;
    }
    
    .action-buttons {
        grid-template-columns: 1fr;
    }
    
    .support-contacts {
        flex-direction: column;
        align-items: stretch;
    }
}
</style>

<script>
function copyReference() {
    const refNumber = '<?= htmlspecialchars($booking_ref) ?>';
    const btn = document.getElementById('copyBtn');
    
    navigator.clipboard.writeText(refNumber).then(() => {
        btn.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            Copied!
        `;
        btn.style.background = '#10b981';
        btn.style.color = 'white';
        btn.style.borderColor = '#10b981';
        
        setTimeout(() => {
            btn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                </svg>
                Copy
            `;
            btn.style.background = 'white';
            btn.style.color = '#6b7280';
            btn.style.borderColor = '#e5e7eb';
        }, 2000);
    });
}
</script>

<?php include 'footer.php'; ?>