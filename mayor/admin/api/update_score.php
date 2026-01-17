<?php
// admin/api/update_score.php
require_once __DIR__.'/../../inc/security.php';
require_once __DIR__.'/../../inc/db.php';
require_valid_csrf();
require_ref_or_admin();

// JSON payload or POST
$payload = $_POST['payload'] ?? file_get_contents('php://input');
$data = json_decode($payload, true);
if (!$data) { http_response_code(400); echo json_encode(['error'=>'invalid payload']); exit; }

$match_id = (int)($data['match_id'] ?? 0);
$home_score = (int)($data['home_score'] ?? 0);
$away_score = (int)($data['away_score'] ?? 0);
$details = json_encode($data['details'] ?? null);

$stmt = $pdo->prepare('INSERT INTO results (match_id,home_score,away_score,details,recorded_by) VALUES (?,?,?,?,?)');
$stmt->execute([$match_id,$home_score,$away_score,$details,$_SESSION['user_id']]);

// Update matches.status optional
$pdo->prepare('UPDATE matches SET status=? WHERE id=?')->execute(['live',$match_id]);

echo json_encode(['success'=>true]);
