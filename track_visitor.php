<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
/**
 * Website Visitor Tracker - Server-side Script
 * 
 * This script receives visitor data from the client-side JavaScript,
 * filters out bot traffic, and stores legitimate visitor information.
 */

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Database configuration
$db_host = 'localhost';
$db_user = 'matatieleco_visitors'; // Change to your database user
$db_password = 'jXJJzRE4eKpsPkqtMdwt'; // Change to your database password
$db_name = 'matatieleco_visitors'; // Change to your database name

// Create connection
$conn = new mysqli($db_host, $db_user, $db_password, $db_name);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

// Set charset to utf8
$conn->set_charset("utf8");

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (!isset($data['visitor_id']) || !isset($data['user_agent']) || !isset($data['page_url'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Missing required fields']));
}

// Extract data
$visitor_id = sanitize_input($data['visitor_id']);
$user_agent = sanitize_input($data['user_agent']);
$page_url = sanitize_input($data['page_url']);
$referrer = isset($data['referrer']) ? sanitize_input($data['referrer']) : null;
$device_type = isset($data['device_type']) ? sanitize_input($data['device_type']) : 'Unknown';
$latitude = isset($data['latitude']) ? floatval($data['latitude']) : null;
$longitude = isset($data['longitude']) ? floatval($data['longitude']) : null;

// Get client IP address
$ip_address = get_client_ip();

// Check if visitor is a bot
$is_bot = detect_bot($user_agent, $ip_address);

// Get location from IP if geolocation not provided
$location = get_location_from_ip($ip_address);
$country = $location['country'] ?? 'Unknown';
$city = $location['city'] ?? 'Unknown';

// If geolocation API provided coordinates, try to get more accurate location
if ($latitude && $longitude) {
    $geo_location = get_location_from_coordinates($latitude, $longitude);
    if ($geo_location) {
        $country = $geo_location['country'] ?? $country;
        $city = $geo_location['city'] ?? $city;
    }
}

// Prepare SQL statement
//$stmt = $conn->prepare("INSERT INTO visitors (visitor_id, ip_address, user_agent, referrer, page_visited, country, city, device_type, is_bot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt = $conn->prepare("INSERT INTO visitors (visitor_id, ip_address, user_agent, referrer, page_visited, country, city, device_type, is_bot, visit_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

if (!$stmt) {
    http_response_code(500);
    die(json_encode(['error' => 'Prepare failed: ' . $conn->error]));
}

// Bind parameters
$stmt->bind_param("ssssssssi", $visitor_id, $ip_address, $user_agent, $referrer, $page_url, $country, $city, $device_type, $is_bot);

// Execute statement
if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Visitor tracked successfully']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
}

$stmt->close();
$conn->close();

/**
 * Sanitize user input
 */
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Get client IP address
 */
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Detect if the visitor is a bot
 * Uses multiple detection methods
 */
function detect_bot($user_agent, $ip_address) {
    // List of common bot user agents
    $bot_patterns = array(
        'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget', 'python',
        'java(?!script)', 'perl', 'ruby', 'php', 'go-http-client',
        'googlebot', 'bingbot', 'slurp', 'duckduckbot', 'baiduspider',
        'yandexbot', 'facebookexternalhit', 'twitterbot', 'linkedinbot',
        'whatsapp', 'telegram', 'viber', 'skype', 'slack', 'discord',
        'semrush', 'ahrefs', 'mj12bot', 'dotbot', 'majestic', 'rogerbot',
        'grapeshot', 'petalbot', 'sogou', 'qwantify', 'applebot', 'pingdom',
        'uptime', 'monitoring', 'check', 'test', 'validator', 'scanner',
        'nikto', 'nmap', 'masscan', 'nessus', 'qualys', 'acunetix',
        'metasploit', 'sqlmap', 'havij', 'joomla', 'wordpress', 'drupal',
        'magento', 'prestashop', 'opencart', 'oscommerce', 'zen cart'
    );

    $user_agent_lower = strtolower($user_agent);

    // Check against bot patterns
    foreach ($bot_patterns as $pattern) {
        if (strpos($user_agent_lower, $pattern) !== false) {
            return true;
        }
    }

    // Check for missing or suspicious user agents
    if (empty($user_agent) || strlen($user_agent) < 10) {
        return true;
    }

    // Check for headless browsers
    if (strpos($user_agent_lower, 'headless') !== false) {
        return true;
    }

    // Check for automation tools
    if (strpos($user_agent_lower, 'phantomjs') !== false ||
        strpos($user_agent_lower, 'selenium') !== false ||
        strpos($user_agent_lower, 'puppeteer') !== false) {
        return true;
    }

    return false;
}

/**
 * Get location from IP address using free IP geolocation API
 * Uses ip-api.com (free tier: 45 requests per minute)
 */
function get_location_from_ip($ip_address) {
    // Don't query for private IPs
    if (is_private_ip($ip_address)) {
        return ['country' => 'Private Network', 'city' => 'Unknown'];
    }

    $cache_dir = __DIR__ . '/ip_cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . '/' . md5($ip_address) . '.json';

    // Check cache first (valid for 30 days)
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 2592000) {
        return json_decode(file_get_contents($cache_file), true);
    }

    // Query IP geolocation API
    $url = 'http://ip-api.com/json/' . $ip_address . '?fields=country,city,status';
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'VisitorTracker/1.0'
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return ['country' => 'Unknown', 'city' => 'Unknown'];
    }

    $data = json_decode($response, true);

    if ($data && $data['status'] === 'success') {
        $location = [
            'country' => $data['country'] ?? 'Unknown',
            'city' => $data['city'] ?? 'Unknown'
        ];
        
        // Cache the result
        file_put_contents($cache_file, json_encode($location));
        
        return $location;
    }

    return ['country' => 'Unknown', 'city' => 'Unknown'];
}

/**
 * Check if IP is private
 */
function is_private_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
}

/**
 * Get location from coordinates using Nominatim (OpenStreetMap)
 * This is optional and provides more accurate location data
 */
function get_location_from_coordinates($latitude, $longitude) {
    $cache_dir = __DIR__ . '/geo_cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_key = md5($latitude . ',' . $longitude);
    $cache_file = $cache_dir . '/' . $cache_key . '.json';

    // Check cache first (valid for 30 days)
    if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 2592000) {
        return json_decode(file_get_contents($cache_file), true);
    }

    // Query Nominatim API
    $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . $latitude . '&lon=' . $longitude;
    
    $context = stream_context_create([
        'http' => [
            'timeout' => 5,
            'user_agent' => 'VisitorTracker/1.0'
        ]
    ]);

    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        return null;
    }

    $data = json_decode($response, true);

    if ($data && isset($data['address'])) {
        $location = [
            'country' => $data['address']['country'] ?? 'Unknown',
            'city' => $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? 'Unknown'
        ];
        
        // Cache the result
        file_put_contents($cache_file, json_encode($location));
        
        return $location;
    }

    return null;
}
?>