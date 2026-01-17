<?php
require_once __DIR__ . '/../inc/db.php';
header('Content-Type: application/json');

$sport_id = (int)($_GET['sport_id'] ?? 0);
if ($sport_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name FROM teams WHERE sport_id = ? ORDER BY name");
$stmt->execute([$sport_id]);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
