<?php
require '../includes/config.php';
require '../includes/functions.php';
$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM attractions WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
echo json_encode($row);
