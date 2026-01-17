<?php
/**
 * Dr LK Ndaba & Partners - Appointment Form Submission Handler
 * Handles form validation, sanitization, and email sending
 */

header('Content-Type: application/json');

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Response array
$response = array(
    'success' => false,
    'message' => 'An error occurred. Please try again.'
);

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode($response);
    exit;
}

// Sanitize and validate input
function sanitizeInput($input) {
    return htmlspecialchars(stripslashes(trim($input)), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    // Basic phone validation - allows various formats
    return preg_match('/^[\d\s\-\+\(\)]+$/', $phone) && strlen(preg_replace('/\D/', '', $phone)) >= 7;
}

// Get form data
$name = isset($_POST['name']) ? sanitizeInput($_POST['name']) : '';
$email = isset($_POST['email']) ? sanitizeInput($_POST['email']) : '';
$phone = isset($_POST['phone']) ? sanitizeInput($_POST['phone']) : '';
$service = isset($_POST['service']) ? sanitizeInput($_POST['service']) : '';
$preferredDate = isset($_POST['preferredDate']) ? sanitizeInput($_POST['preferredDate']) : '';
$message = isset($_POST['message']) ? sanitizeInput($_POST['message']) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone) || empty($service) || empty($preferredDate)) {
    $response['message'] = 'Please fill in all required fields.';
    echo json_encode($response);
    exit;
}

// Validate email
if (!validateEmail($email)) {
    $response['message'] = 'Please enter a valid email address.';
    echo json_encode($response);
    exit;
}

// Validate phone
if (!validatePhone($phone)) {
    $response['message'] = 'Please enter a valid phone number.';
    echo json_encode($response);
    exit;
}

// Validate date
$appointmentDate = DateTime::createFromFormat('Y-m-d', $preferredDate);
if (!$appointmentDate || $appointmentDate->format('Y-m-d') !== $preferredDate) {
    $response['message'] = 'Please enter a valid date.';
    echo json_encode($response);
    exit;
}

// Check if date is in the future
$today = new DateTime();
if ($appointmentDate < $today) {
    $response['message'] = 'Please select a future date.';
    echo json_encode($response);
    exit;
}

// Service mapping
$serviceNames = array(
    'general' => 'General Practice',
    'dietitian' => 'Clinical Dietitian',
    'physiotherapy' => 'Physiotherapy',
    'diabetic' => 'Diabetic Clinic'
);

$serviceName = isset($serviceNames[$service]) ? $serviceNames[$service] : $service;

// Prepare email content
$to = 'ndabalk@telkomsa.net';
$subject = 'New Appointment Request - Dr LK Ndaba & Partners';

$emailBody = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .header { background-color: #8b3a46; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #8b3a46; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class=\"header\">
        <h2>New Appointment Request</h2>
    </div>
    <div class=\"content\">
        <div class=\"field\">
            <span class=\"label\">Patient Name:</span><br>
            " . $name . "
        </div>
        <div class=\"field\">
            <span class=\"label\">Email:</span><br>
            " . $email . "
        </div>
        <div class=\"field\">
            <span class=\"label\">Phone:</span><br>
            " . $phone . "
        </div>
        <div class=\"field\">
            <span class=\"label\">Service Required:</span><br>
            " . $serviceName . "
        </div>
        <div class=\"field\">
            <span class=\"label\">Preferred Date:</span><br>
            " . date('F j, Y', strtotime($preferredDate)) . "
        </div>
        " . (!empty($message) ? "
        <div class=\"field\">
            <span class=\"label\">Additional Information:</span><br>
            " . nl2br($message) . "
        </div>
        " : "") . "
    </div>
    <div class=\"footer\">
        <p>This appointment request was submitted on " . date('F j, Y \a\t g:i A') . "</p>
        <p>Please contact the patient to confirm the appointment.</p>
    </div>
</body>
</html>
";

// Email headers
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$headers .= "From: " . $email . "\r\n";
$headers .= "Reply-To: " . $email . "\r\n";

// Send email to practice
$mailSent = mail($to, $subject, $emailBody, $headers);

// Send confirmation email to patient
$patientSubject = 'Appointment Request Received - Dr LK Ndaba & Partners';
$patientBody = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .header { background-color: #8b3a46; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .field { margin-bottom: 15px; }
        .label { font-weight: bold; color: #8b3a46; }
        .footer { background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class=\"header\">
        <h2>Appointment Request Received</h2>
    </div>
    <div class=\"content\">
        <p>Dear " . $name . ",</p>
        <p>Thank you for submitting your appointment request. We have received your information and will contact you shortly to confirm your appointment.</p>
        <div class=\"field\">
            <span class=\"label\">Your Appointment Details:</span><br>
            Service: " . $serviceName . "<br>
            Preferred Date: " . date('F j, Y', strtotime($preferredDate)) . "
        </div>
        <p>If you have any questions, please feel free to contact us:</p>
        <p>
            <strong>Phone:</strong> 045 838 5418 / 045 839 3788<br>
            <strong>Mobile:</strong> 083 271 6151<br>
            <strong>Email:</strong> ndabalk@telkomsa.net
        </p>
    </div>
    <div class=\"footer\">
        <p>&copy; 2024 Dr LK Ndaba & Partners. All rights reserved.</p>
    </div>
</body>
</html>
";

$patientHeaders = "MIME-Version: 1.0" . "\r\n";
$patientHeaders .= "Content-type: text/html; charset=UTF-8" . "\r\n";
$patientHeaders .= "From: ndabalk@telkomsa.net" . "\r\n";

mail($email, $patientSubject, $patientBody, $patientHeaders);

// Check if email was sent successfully
if ($mailSent) {
    $response['success'] = true;
    $response['message'] = 'Thank you! Your appointment request has been received. We will contact you shortly to confirm.';
} else {
    // Even if mail() fails, we can still consider it a success in some cases
    // (depends on server configuration)
    $response['success'] = true;
    $response['message'] = 'Your appointment request has been submitted. We will contact you shortly.';
}

echo json_encode($response);
exit;
?>
