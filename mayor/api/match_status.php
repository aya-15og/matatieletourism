<?php
// public/api/match_status.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__.'/../inc/db.php';

$mid = isset($_GET['mid']) ? (int)$_GET['mid'] : null;
if (!$mid) { echo json_encode(['error'=>'missing match id']); exit; }

// get match + latest result
$stmt = $pdo->prepare('SELECT m.*, s.name as sport, t1.name home_name, t2.name away_name FROM matches m JOIN sports s ON s.id=m.sport_id JOIN teams t1 ON t1.id=m.home_team JOIN teams t2 ON t2.id=m.away_team WHERE m.id=?');
$stmt->execute([$mid]); $match = $stmt->fetch();
if (!$match) { echo json_encode(['error'=>'match not found']); exit; }

$stmt = $pdo->prepare('SELECT * FROM results WHERE match_id=? ORDER BY recorded_at DESC LIMIT 1');
$stmt->execute([$mid]); $res = $stmt->fetch();

echo json_encode([
    'match' => $match,
    'result' => $res,
    'details' => $res ? json_decode($res['details'], true) : null
]);
