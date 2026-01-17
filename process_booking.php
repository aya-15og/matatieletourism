<?php
session_start();
require 'includes/config.php';
require 'includes/email_functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: stays.php");
    exit;
}

// Sanitize and validate inputs
$stay_id = intval($_POST['stay_id']);
$is_partner = intval($_POST['is_partner']);
$guest_name = trim($_POST['guest_name']);
$guest_email = trim($_POST['guest_email']);
$guest_phone = trim($_POST['guest_phone']);
$check_in_date = $_POST['check_in_date'];
$check_out_date = $_POST['check_out_date'];
$number_of_guests = intval($_POST['number_of_guests']);
$number_of_nights = intval($_POST['number_of_nights']);
$total_amount = floatval($_POST['total_amount']);
$commission_amount = floatval($_POST['commission_amount']);
$special_requests = trim($_POST['special_requests'] ?? '');

// Validate required fields
if (!$stay_id || !$guest_name || !$guest_email || !$guest_phone || 
    !$check_in_date || !$check_out_date || !$number_of_guests) {
    $_SESSION['booking_error'] = "Please fill in all required fields.";
    header("Location: booking.php?stay_id=" . $stay_id);
    exit;
}

// Validate email
if (!filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['booking_error'] = "Please provide a valid email address.";
    header("Location: booking.php?stay_id=" . $stay_id);
    exit;
}

// Validate dates
$check_in = new DateTime($check_in_date);
$check_out = new DateTime($check_out_date);
$today = new DateTime();
$today->setTime(0, 0, 0);

if ($check_in < $today) {
    $_SESSION['booking_error'] = "Check-in date cannot be in the past.";
    header("Location: booking.php?stay_id=" . $stay_id);
    exit;
}

if ($check_out <= $check_in) {
    $_SESSION['booking_error'] = "Check-out date must be after check-in date.";
    header("Location: booking.php?stay_id=" . $stay_id);
    exit;
}

// Get stay details
$stmt = $pdo->prepare("SELECT * FROM stays WHERE id = ?");
$stmt->execute([$stay_id]);
$stay = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$stay) {
    $_SESSION['booking_error'] = "Accommodation not found.";
    header("Location: stays.php");
    exit;
}

// Generate unique booking reference
$booking_reference = 'MTT-' . strtoupper(substr(uniqid(), -8));

// Determine booking status based on partner status
$booking_status = $is_partner ? 'confirmed' : 'pending';

try {
    // Insert booking into database
    $stmt = $pdo->prepare("
        INSERT INTO bookings (
            stay_id, guest_name, guest_email, guest_phone, 
            check_in_date, check_out_date, number_of_guests, 
            number_of_nights, total_amount, commission_amount, 
            special_requests, booking_status, payment_status, 
            is_partner_booking, booking_reference
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', ?, ?)
    ");
    
    $stmt->execute([
        $stay_id, $guest_name, $guest_email, $guest_phone,
        $check_in_date, $check_out_date, $number_of_guests,
        $number_of_nights, $total_amount, $commission_amount,
        $special_requests, $booking_status, $is_partner, $booking_reference
    ]);
    
    $booking_id = $pdo->lastInsertId();
    
    // Prepare booking data for emails
    $booking_data = [
        'booking_id' => $booking_id,
        'booking_reference' => $booking_reference,
        'stay_name' => $stay['name'],
        'stay_location' => $stay['location'],
        'stay_contact' => $stay['contact'],
        'guest_name' => $guest_name,
        'guest_email' => $guest_email,
        'guest_phone' => $guest_phone,
        'check_in_date' => $check_in->format('F d, Y'),
        'check_out_date' => $check_out->format('F d, Y'),
        'number_of_guests' => $number_of_guests,
        'number_of_nights' => $number_of_nights,
        'total_amount' => number_format($total_amount, 2),
        'commission_amount' => number_format($commission_amount, 2),
        'special_requests' => $special_requests,
        'is_partner' => $is_partner,
        'booking_status' => $booking_status
    ];
    
    // Send emails based on partner status
    if ($is_partner) {
        // Partner booking - send confirmation to guest
        send_partner_booking_confirmation($guest_email, $booking_data);
        
        // Send notification to property
        $property_email = $stay['booking_email'] ?: $stay['contact'];
        send_partner_booking_notification($property_email, $booking_data);
        
        // Send notification to admin
        send_admin_booking_notification('admin@matatiele.co.za', $booking_data);
        
        $_SESSION['booking_success'] = [
            'type' => 'partner',
            'reference' => $booking_reference,
            'stay_name' => $stay['name']
        ];
    } else {
        // Non-partner inquiry - send inquiry email
        $property_email = $stay['booking_email'] ?: $stay['contact'];
        send_inquiry_to_property($property_email, $booking_data);
        
        // Send inquiry confirmation to guest
        send_inquiry_confirmation_to_guest($guest_email, $booking_data);
        
        // Send notification to admin
        send_admin_inquiry_notification('admin@matatiele.co.za', $booking_data);
        
        $_SESSION['booking_success'] = [
            'type' => 'inquiry',
            'reference' => $booking_reference,
            'stay_name' => $stay['name']
        ];
    }
    
    // Redirect to success page
    header("Location: booking_success.php?ref=" . $booking_reference);
    exit;
    
} catch (Exception $e) {
    error_log("Booking Error: " . $e->getMessage());
    $_SESSION['booking_error'] = "An error occurred while processing your booking. Please try again or contact us directly.";
    header("Location: booking.php?stay_id=" . $stay_id);
    exit;
}