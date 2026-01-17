<?php
session_start();
if (!isset($_SESSION['admin'])) {
    http_response_code(403);
    exit('Unauthorized');
}

require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order = $_POST['order'] ?? [];
    $table = $_POST['table'] ?? '';
    
    // Validate table name to prevent SQL injection
    $allowed_tables = ['dining', 'activities', 'stays', 'attractions'];
    if (!in_array($table, $allowed_tables)) {
        exit('Invalid table');
    }
    
    foreach ($order as $item) {
        $id = intval($item['id']);
        $sort_order = intval($item['sort_order']);
        $stmt = $pdo->prepare("UPDATE $table SET sort_order = ? WHERE id = ?");
        $stmt->execute([$sort_order, $id]);
    }
    
    echo json_encode(['success' => true]);
}
?>