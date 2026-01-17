<?php
header('Content-Type: application/json');
require '../includes/config.php';

// Get attraction ID from GET parameter
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid attraction ID'
    ]);
    exit;
}

try {
    // Fetch attraction details
    $stmt = $pdo->prepare("SELECT * FROM attractions WHERE id = ?");
    $stmt->execute([$id]);
    $attraction = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$attraction) {
        echo json_encode([
            'success' => false,
            'error' => 'Attraction not found'
        ]);
        exit;
    }
    
    // Fetch related attractions (same category, exclude current)
    $related_stmt = $pdo->prepare("
        SELECT id, name, description, image, category 
        FROM attractions 
        WHERE category = ? AND id != ? 
        ORDER BY RAND() 
        LIMIT 3
    ");
    $related_stmt->execute([$attraction['category'], $id]);
    $related = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the response
    $response = [
        'success' => true,
        'data' => [
            'id' => $attraction['id'],
            'name' => $attraction['name'],
            'description' => $attraction['description'],
            'location' => $attraction['location'],
            'contact' => $attraction['contact'],
            'category' => $attraction['category'],
            'image' => $attraction['image'] ?? 'images/placeholder.jpg',
            'featured' => (bool)$attraction['featured'],
            'created_at' => $attraction['created_at']
        ],
        'related' => $related
    ];
    
    echo json_encode($response, JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ]);
}
?>