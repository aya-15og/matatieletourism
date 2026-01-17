<?php
/**
 * Sara-Lee Guesthouse Configuration File
 * 
 * This file contains all configuration settings for the website
 */

// Database Configuration (if needed for future enhancements)
define('DB_HOST', 'localhost');
define('DB_NAME', 'saralee_db');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// Site Configuration
define('SITE_NAME', 'Sara-Lee Guesthouse');
define('SITE_URL', 'https://www.sara-lee.co.za');
define('SITE_EMAIL', 'bookings@sara-lee.co.za');
define('SITE_PHONE', '079 891 1983');
define('SITE_ADDRESS', 'Hermanus Road, South Africa');

// Email Configuration
define('ADMIN_EMAIL', 'bookings@sara-lee.co.za');
define('FROM_EMAIL', 'noreply@sara-lee.co.za');
define('FROM_NAME', 'Sara-Lee Guesthouse');

// Room Prices (can be easily updated)
$room_prices = [
    'deluxe' => 850,
    'standard' => 650,
    'family' => 1200
];

// Business Hours
$business_hours = [
    'reception' => '24 Hours',
    'checkin_start' => '14:00',
    'checkin_end' => '19:00',
    'checkout' => '10:00',
    'breakfast_start' => '07:00',
    'breakfast_end' => '09:30'
];

// Social Media Links
$social_media = [
    'facebook' => 'https://facebook.com/saraleeguesthouse',
    'instagram' => 'https://instagram.com/saraleeguesthouse',
    'tripadvisor' => 'https://tripadvisor.com/saraleeguesthouse'
];

// Google Maps Embed (replace with actual coordinates)
define('GOOGLE_MAPS_EMBED', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3310.123456789!2d18.123456!3d-34.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzTCsDA3JzI0LjQiUyAxOMKwMDcnMjQuNCJF!5e0!3m2!1sen!2sza!4v1234567890');

// SEO Settings
$seo = [
    'keywords' => 'guesthouse, bed and breakfast, accommodation, Hermanus Road, South Africa, B&B, lodging',
    'description' => 'Sara-Lee Guesthouse offers comfortable bed and breakfast accommodation on Hermanus Road. Book your stay today!',
    'author' => 'Sara-Lee Guesthouse'
];

// Timezone
date_default_timezone_set('Africa/Johannesburg');

// Error Reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Helper Functions
function formatPrice($amount) {
    return 'R' . number_format($amount, 2);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function sendEmail($to, $subject, $message, $from = FROM_EMAIL) {
    $headers = "From: " . FROM_NAME . " <$from>\r\n";
    $headers .= "Reply-To: $from\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Database Connection (optional - for future use)
function getDatabaseConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}
?>