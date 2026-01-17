<?php
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_admin(); // only admin can export

$what = $_GET['what'] ?? null;
$filename = 'export.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$filename);
$out = fopen('php://output', 'w');
if ($what === 'teams') {
    fputcsv($out, ['ID','Sport','Name','Short','Coach','Home']);
    $stmt = $pdo->query('SELECT t.*, s.name as sport FROM teams t JOIN sports s ON s.id=t.sport_id ORDER BY s.name, t.name');
    while ($r = $stmt->fetch()) {
        fputcsv($out, [$r['id'],$r['sport'],$r['name'],$r['short_name'],$r['coach'],$r['home_ground']]);
    }
} elseif ($what === 'fixtures') {
    fputcsv($out, ['ID','Sport','Home','Away','Date','Venue','Status','Stage']);
    $stmt = $pdo->query('SELECT m.*, s.name as sport, t1.name home_name, t2.name away_name FROM matches m JOIN sports s ON s.id=m.sport_id JOIN teams t1 ON t1.id=m.home_team JOIN teams t2 ON t2.id=m.away_team ORDER BY m.match_date');
    while ($r = $stmt->fetch()) {
        fputcsv($out, [$r['id'],$r['sport'],$r['home_name'],$r['away_name'],$r['match_date'],$r['venue'],$r['status'],$r['stage']]);
    }
} elseif ($what === 'results') {
    fputcsv($out, ['MatchID','Sport','Home','Away','HomeScore','AwayScore','Details','RecordedAt']);
    $stmt = $pdo->query('SELECT r.*, m.sport_id, m.home_team, m.away_team, t1.name home_name, t2.name away_name FROM results r JOIN matches m ON m.id=r.match_id JOIN teams t1 ON t1.id=m.home_team JOIN teams t2 ON t2.id=m.away_team ORDER BY r.recorded_at DESC');
    while ($r = $stmt->fetch()) {
        fputcsv($out, [$r['match_id'],$r['sport_id'],$r['home_name'],$r['away_name'],$r['home_score'],$r['away_score'],$r['details'],$r['recorded_at']]);
    }
} else {
    fputcsv($out, ['message','unknown export']);
}
fclose($out);
exit;
