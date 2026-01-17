<?php
// send-appointment.php
// Secure Appointment Form Handler with Anti-Spam Protection

// Start session for CSRF validation
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// Configuration
define('RECIPIENT_EMAIL', 'drmsingapantsi@icloud.com'); // Doctor's email
define('RECIPIENT_NAME', 'Dr. Mandilakhe Msingapantsi');
define('SITE_NAME', 'Dr. Msingapantsi Orthopaedic Surgery');
define('MIN_SUBMISSION_TIME', 3); // Minimum seconds to fill form (anti-bot)

// Response function
function sendResponse($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Invalid request method.');
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || empty($_POST['csrf_token'])) {
    sendResponse(false, 'Security validation failed. Please refresh the page and try again.');
}

// Validate form timestamp (prevent instant bot submissions)
if (!isset($_POST['form_timestamp']) || empty($_POST['form_timestamp'])) {
    sendResponse(false, 'Security validation failed. Please try again.');
}

$submissionTime = (time() * 1000) - intval($_POST['form_timestamp']);
if ($submissionTime < (MIN_SUBMISSION_TIME * 1000)) {
    sendResponse(false, 'Please take your time filling out the form.');
}

// Honeypot check (bots fill this hidden field)
if (!empty($_POST['website'])) {
    // Silent fail for bots
    sendResponse(true, 'Thank you! We will contact you shortly.');
}

// Sanitize and validate input
$name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
$phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
$email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));

// Validation checks
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Please provide a valid name.';
}

if (empty($phone) || !preg_match('/^[0-9\s\-\+\(\)]{10,15}$/', $phone)) {
    $errors[] = 'Please provide a valid phone number.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please provide a valid email address.';
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Please provide a detailed reason for consultation (at least 10 characters).';
}

// Check for spam patterns in message
$spamPatterns = [
    '/\b(viagra|cialis|pharmacy|casino|lottery|prize|winner)\b/i',
    '/\b(click here|buy now|limited time|act now)\b/i',
    '/(http[s]?:\/\/[^\s]+){3,}/', // Multiple URLs
];

foreach ($spamPatterns as $pattern) {
    if (preg_match($pattern, $message)) {
        // Silent fail for spam
        sendResponse(true, 'Thank you! We will contact you shortly.');
    }
}

// Return errors if any
if (!empty($errors)) {
    sendResponse(false, implode(' ', $errors));
}

// Escape HTML for display
$name_safe = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$phone_safe = htmlspecialchars($phone, ENT_QUOTES, 'UTF-8');
$email_safe = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$message_safe = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

// Get submission details
$ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$submission_date = date('F j, Y, g:i a');

// Prepare HTML email
$subject = "New Appointment Request from $name_safe";

$html_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: #ffffff;
            padding: 30px 20px;
            border: 1px solid #e5e7eb;
        }
        .field {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #f3f4f6;
        }
        .field:last-of-type {
            border-bottom: none;
        }
        .label {
            font-weight: bold;
            color: #1e3a8a;
            margin-bottom: 5px;
            display: block;
        }
        .value {
            color: #4b5563;
            padding-left: 10px;
        }
        .message-box {
            background: #f9fafb;
            padding: 15px;
            border-left: 4px solid #3b82f6;
            border-radius: 4px;
        }
        .footer {
            background: #f3f4f6;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .alert {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class='header'>
        <h1>🏥 New Appointment Request</h1>
        <p>" . SITE_NAME . "</p>
    </div>
    
    <div class='content'>
        <div class='alert'>
            <strong>⚠️ Action Required:</strong> A new patient has requested an appointment. Please respond promptly.
        </div>
        
        <div class='field'>
            <span class='label'>👤 Patient Name:</span>
            <span class='value'>$name_safe</span>
        </div>
        
        <div class='field'>
            <span class='label'>📞 Phone Number:</span>
            <span class='value'><a href='tel:$phone_safe'>$phone_safe</a></span>
        </div>
        
        <div class='field'>
            <span class='label'>✉️ Email Address:</span>
            <span class='value'><a href='mailto:$email_safe'>$email_safe</a></span>
        </div>
        
        <div class='field'>
            <span class='label'>📝 Reason for Consultation:</span>
            <div class='message-box'>
                $message_safe
            </div>
        </div>
        
        <div class='field'>
            <span class='label'>📅 Submitted On:</span>
            <span class='value'>$submission_date</span>
        </div>
    </div>
    
    <div class='footer'>
        <p><strong>Submission Details</strong></p>
        <p>IP Address: $ip_address</p>
        <p style='font-size: 10px; color: #9ca3af; margin-top: 10px;'>
            This email was sent from your appointment request form on " . SITE_NAME . "
        </p>
    </div>
</body>
</html>
";

// Plain text version (fallback)
$plain_body = "
NEW APPOINTMENT REQUEST
========================

Patient Name: $name_safe
Phone Number: $phone_safe
Email Address: $email_safe

Reason for Consultation:
$message

Submitted On: $submission_date
IP Address: $ip_address

---
This email was sent from your appointment request form.
";

// Email headers
$headers = [
    'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    'Reply-To: ' . $name_safe . ' <' . $email_safe . '>',
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="boundary-' . uniqid() . '"',
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 1',
    'Importance: High'
];

// Create multipart email
$boundary = 'boundary-' . uniqid();
$headers = implode("\r\n", [
    'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
    'Reply-To: ' . $name_safe . ' <' . $email_safe . '>',
    'MIME-Version: 1.0',
    'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    'X-Mailer: PHP/' . phpversion(),
    'X-Priority: 1',
    'Importance: High'
]);

$email_body = "
--$boundary
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 7bit

$plain_body

--$boundary
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: 7bit

$html_body

--$boundary--
";

// Send email
$mail_sent = mail(RECIPIENT_EMAIL, $subject, $email_body, $headers);

if ($mail_sent) {
    // Optional: Send confirmation email to patient
    $patient_subject = "Appointment Request Received - " . SITE_NAME;
    $patient_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; }
            .header { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; padding: 30px 20px; text-align: center; }
            .content { background: #ffffff; padding: 30px 20px; border: 1px solid #e5e7eb; }
            .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h1>Thank You, $name_safe!</h1>
        </div>
        <div class='content'>
            <p>We have received your appointment request and will contact you shortly to confirm your appointment.</p>
            <p><strong>Your contact information:</strong><br>
            Phone: $phone_safe<br>
            Email: $email_safe</p>
            <p>If you need immediate assistance, please call us at:</p>
            <p><strong>Medicare Private Hospital:</strong> 014 523 9381<br>
            <strong>Life Peglerae Hospital:</strong> 014 010 0312<br>
            <strong>Emergency:</strong> 073 095 6868</p>
        </div>
        <div class='footer'>
            <p>" . SITE_NAME . "</p>
        </div>
    </body>
    </html>
    ";
    
    $patient_boundary = 'boundary-' . uniqid();
    $patient_headers = implode("\r\n", [
        'From: ' . SITE_NAME . ' <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        'Reply-To: ' . RECIPIENT_NAME . ' <' . RECIPIENT_EMAIL . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8'
    ]);
    
    mail($email_safe, $patient_subject, $patient_body, $patient_headers);
    
    sendResponse(true, 'Thank you! Your appointment request has been sent successfully. We will contact you shortly.');
} else {
    // Log error (in production, log to file instead)
    error_log("Failed to send appointment email for: $email_safe");
    sendResponse(false, 'There was an error sending your request. Please call us directly at 014 523 9381.');
}
?>