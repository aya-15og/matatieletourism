<?php
/**
 * Ad Click Tracking Script
 * Handles AJAX requests to track advertisement clicks
 */

header('Content-Type: application/json');

require 'includes/config.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($data['ad_id']) || !is_numeric($data['ad_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ad ID']);
    exit;
}

$ad_id = intval($data['ad_id']);

try {
    // Verify ad exists and is active
    $stmt = $pdo->prepare("
        SELECT id FROM ads 
        WHERE id = ? 
        AND visible = 1
        AND (start_date IS NULL OR start_date <= CURDATE())
        AND (end_date IS NULL OR end_date >= CURDATE())
    ");
    $stmt->execute([$ad_id]);
    
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Ad not found or inactive']);
        exit;
    }
    
    // Update click count
    $update = $pdo->prepare("UPDATE ads SET clicks = clicks + 1 WHERE id = ?");
    $update->execute([$ad_id]);
    
    // Optional: Log click with additional details (IP, user agent, timestamp)
    // This can be useful for fraud detection and analytics
    $log_stmt = $pdo->prepare("
        INSERT INTO ad_clicks_log (ad_id, ip_address, user_agent, clicked_at) 
        VALUES (?, ?, ?, NOW())
    ");
    
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    try {
        $log_stmt->execute([$ad_id, $ip_address, $user_agent]);
    } catch (PDOException $e) {
        // Table might not exist yet - that's okay, continue
        error_log("Ad clicks log error: " . $e->getMessage());
    }
    
    echo json_encode([
        'success' => true,
        'ad_id' => $ad_id,
        'message' => 'Click tracked successfully'
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Ad tracking error: " . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}
?>