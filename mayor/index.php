<?php
declare(strict_types=1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__.'/inc/db.php';

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// --- Upcoming matches ---
$upcoming = $pdo->query("
    SELECT m.*, s.name as sport, t1.name as home_name, t2.name as away_name
    FROM matches m
    JOIN sports s ON s.id = m.sport_id
    JOIN teams t1 ON t1.id = m.home_team
    JOIN teams t2 ON t2.id = m.away_team
    WHERE m.status IN ('scheduled','live') AND m.match_date >= NOW()
    ORDER BY m.match_date ASC
    LIMIT 12
")->fetchAll();

// --- Recent results ---
$results = $pdo->query("
    SELECT r.*, m.*, t1.name as home_name, t2.name as away_name, s.name as sport
    FROM results r
    JOIN matches m ON m.id = r.match_id
    JOIN sports s ON s.id = m.sport_id
    JOIN teams t1 ON t1.id = m.home_team
    JOIN teams t2 ON t2.id = m.away_team
    WHERE r.archived = 0
    ORDER BY r.recorded_at DESC
    LIMIT 12
")->fetchAll();

// --- Calculate standings per sport ---
$sports = $pdo->query("SELECT * FROM sports")->fetchAll();
$standings = [];
foreach($sports as $sport){
    $stmt = $pdo->prepare("
        SELECT m.id, m.home_team, m.away_team, r.home_score, r.away_score 
        FROM matches m 
        JOIN results r ON r.match_id = m.id 
        WHERE m.sport_id = ?
    ");
    $stmt->execute([$sport['id']]);
    $rows = $stmt->fetchAll();

    $table = [];
    foreach($rows as $r){
        foreach(['home_team','away_team'] as $tkey){
            if(!isset($table[$r[$tkey]])) $table[$r[$tkey]]=['played'=>0,'win'=>0,'draw'=>0,'loss'=>0,'gf'=>0,'ga'=>0,'pts'=>0];
        }
        $ht = $r['home_team']; $at = $r['away_team'];
        $hs = (int)$r['home_score']; $as = (int)$r['away_score'];
        $table[$ht]['played']++; $table[$at]['played']++;
        $table[$ht]['gf']+=$hs; $table[$ht]['ga']+=$as;
        $table[$at]['gf']+=$as; $table[$at]['ga']+=$hs;
        if($hs>$as){ $table[$ht]['win']++; $table[$ht]['pts']+=3; $table[$at]['loss']++; }
        elseif($hs<$as){ $table[$at]['win']++; $table[$at]['pts']+=3; $table[$ht]['loss']++; }
        else { $table[$ht]['draw']++; $table[$ht]['pts']+=1; $table[$at]['draw']++; $table[$at]['pts']+=1; }
    }

    if($table){
        $ids = array_keys($table);
        $in = implode(',', array_fill(0,count($ids),'?'));
        $stmt = $pdo->prepare("SELECT id,name FROM teams WHERE id IN ($in)");
        $stmt->execute($ids);
        $names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        $sport_standing = [];
        foreach($table as $tid=>$row){
            $row['team_name']=$names[$tid] ?? 'Team '.$tid;
            $sport_standing[]=$row;
        }
        usort($sport_standing,function($a,$b){ if($a['pts']==$b['pts']) return ($b['gf']-$b['ga'])-($a['gf']-$a['ga']); return $b['pts']-$a['pts']; });
        $standings[$sport['name']] = $sport_standing;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mayoral Cup Tournament Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #f8f9fa 100%);
    color: #2c3e50;
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    position: relative;
}

body::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: 
        radial-gradient(circle at 20% 50%, rgba(255, 215, 0, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 80% 80%, rgba(50, 205, 50, 0.08) 0%, transparent 50%),
        radial-gradient(circle at 40% 20%, rgba(255, 69, 0, 0.06) 0%, transparent 50%),
        radial-gradient(circle at 90% 30%, rgba(30, 144, 255, 0.08) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.container {
    position: relative;
    z-index: 1;
}

.navbar {
    background: linear-gradient(90deg, #FFD700, #32CD32, #FF4500, #1E90FF);
    padding: 1rem 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.navbar-brand img {
    height: 80px;
    width: auto;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0,0,0,0.5);
}

.navbar-brand span {
    color: white;
    font-weight: 700;
    font-size: 1.4rem;
}

.btn-admin {
    background: #ffc107;
    color: #000;
    font-weight: 600;
    border: none;
    padding: 0.6rem 2rem;
    transition: all 0.3s ease;
}

.btn-admin:hover {
    background: #ffb300;
    color: #000;
}

.section-title {
    font-weight: 700;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2c3e50;
}

.section-title i {
    font-size: 1.5rem;
}

.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    overflow: hidden;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 28px rgba(0,0,0,0.15);
}

.card-upcoming {
    border-left: 4px solid #667eea;
    margin-bottom: 1rem;
}

.card-result {
    border-left: 4px solid #51cf66;
    margin-bottom: 1rem;
}

.card-body {
    padding: 1.25rem;
}

.match-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.match-sport {
    display: inline-block;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
    margin-top: 0.5rem;
}

.match-datetime {
    color: #6c757d;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.venue {
    color: #667eea;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.score-display {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
    margin: 0.5rem 0;
}

.table-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
}

.sport-title {
    color: #667eea;
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 3px solid #667eea;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #495057;
    font-weight: 600;
    font-size: 0.85rem;
    border: none;
    padding: 0.75rem 0.5rem;
    text-align: center;
}

.table tbody td {
    padding: 0.75rem 0.5rem;
    vertical-align: middle;
    border-color: #f1f3f5;
    text-align: center;
    font-size: 0.9rem;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table tbody tr:first-child {
    background: linear-gradient(135deg, #fff5e6 0%, #ffe8cc 100%);
    font-weight: 600;
}

.alert-custom {
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
    color: #1565c0;
    padding: 1rem 1.5rem;
}

.empty-state {
    text-align: center;
    padding: 3rem 1rem;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}
</style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
<div class="container">
<a class="navbar-brand d-flex align-items-center" href="/">
<img src="<?= $logo_url ?>" alt="Logo">
<span class="ms-3">Mayoral Cup Tournament</span>
</a>
<div class="ms-auto"><a class="btn btn-admin" href="/mayor/login.php"><i class="bi bi-shield-lock"></i> Admin</a></div>
</div>
</nav>

<div class="container py-5">
<div class="row g-4">

<!-- Upcoming Matches -->
<div class="col-lg-4">
<h3 class="section-title">
<i class="bi bi-calendar-event text-primary"></i>
Upcoming Matches
</h3>
<?php if($upcoming): foreach($upcoming as $m): ?>
<div class="card card-upcoming">
<div class="card-body">
<div class="match-title"><?=htmlspecialchars($m['home_name'])?> vs <?=htmlspecialchars($m['away_name'])?></div>
<span class="match-sport"><?=htmlspecialchars($m['sport'])?></span>
<div class="match-datetime">
<i class="bi bi-clock"></i>
<?=date('D, d M Y • H:i',strtotime($m['match_date']))?>
</div>
<div class="venue mt-2">
<i class="bi bi-geo-alt-fill"></i>
<?=htmlspecialchars($m['venue'] ?? 'TBA')?>
</div>
</div>
</div>
<?php endforeach; else: ?>
<div class="empty-state">
<i class="bi bi-calendar-x"></i>
<p>No upcoming matches scheduled</p>
</div>
<?php endif; ?>
</div>

<!-- Recent Results -->
<div class="col-lg-4">
<h3 class="section-title">
<i class="bi bi-trophy text-success"></i>
Recent Results
</h3>
<?php if($results): foreach($results as $r): ?>
<div class="card card-result">
<div class="card-body">
<div class="match-title"><?=htmlspecialchars($r['home_name'])?> vs <?=htmlspecialchars($r['away_name'])?></div>
<div class="score-display text-center">
<?= $r['home_score'] ?> - <?= $r['away_score'] ?>
</div>
<span class="match-sport"><?=htmlspecialchars($r['sport'])?></span>
<div class="match-datetime">
<i class="bi bi-clock-history"></i>
<?=date('D, d M Y',strtotime($r['recorded_at']))?>
</div>
</div>
</div>
<?php endforeach; else: ?>
<div class="empty-state">
<i class="bi bi-trophy"></i>
<p>No results available yet</p>
</div>
<?php endif; ?>
</div>

<!-- Standings -->
<div class="col-lg-4">
<h3 class="section-title">
<i class="bi bi-bar-chart-line text-danger"></i>
Standings
</h3>
<?php if($standings): foreach($standings as $sport_name=>$teams): ?>
<div class="table-container">
<h5 class="sport-title"><?=htmlspecialchars($sport_name)?></h5>
<div class="table-responsive">
<table class="table table-sm">
<thead>
<tr>
<th>#</th>
<th>Team</th>
<th>P</th>
<th>W</th>
<th>D</th>
<th>L</th>
<th>GF</th>
<th>GA</th>
<th>GD</th>
<th>Pts</th>
</tr>
</thead>
<tbody>
<?php $i=1; foreach($teams as $t): ?>
<tr>
<td><?=$i++?></td>
<td style="text-align: left; font-weight: 600;"><?=htmlspecialchars($t['team_name'])?></td>
<td><?=$t['played']?></td>
<td><?=$t['win']?></td>
<td><?=$t['draw']?></td>
<td><?=$t['loss']?></td>
<td><?=$t['gf']?></td>
<td><?=$t['ga']?></td>
<td><?=$t['gf']-$t['ga']?></td>
<td style="font-weight: 700; color: #667eea;"><?=$t['pts']?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endforeach; else: ?>
<div class="empty-state">
<i class="bi bi-bar-chart"></i>
<p>No standings available</p>
</div>
<?php endif; ?>
</div>

</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>