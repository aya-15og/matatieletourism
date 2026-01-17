<?php
declare(strict_types=1);
ini_set('display_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_admin();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// escape helper
function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// simple CSRF for export actions (session)
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(24));

// --- Inputs ---
$sport_id = isset($_GET['sport_id']) && $_GET['sport_id'] !== '' ? (int)$_GET['sport_id'] : null;

// If export CSV requested
if (isset($_GET['export']) && $sport_id) {
    // optional CSRF param check (prevent accidental crawlers)
    if (!isset($_GET['_csrf']) || !hash_equals($_SESSION['_csrf'], $_GET['_csrf'])) {
        http_response_code(403); echo "Forbidden"; exit;
    }

    // Build standings again and stream CSV
    // Gather finished matches with results for sport
    $stmt = $pdo->prepare('
        SELECT m.id, m.home_team, m.away_team, r.home_score, r.away_score
        FROM matches m
        JOIN results r ON r.match_id = m.id
        WHERE m.sport_id = ?
    ');
    $stmt->execute([$sport_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $table = [];
    foreach ($rows as $r) {
        foreach (['home_team', 'away_team'] as $tkey) {
            if (!isset($table[$r[$tkey]])) {
                $table[$r[$tkey]] = ['played'=>0,'win'=>0,'draw'=>0,'loss'=>0,'gf'=>0,'ga'=>0,'pts'=>0];
            }
        }
        $ht = (int)$r['home_team']; $at = (int)$r['away_team'];
        $hs = (int)$r['home_score']; $as = (int)$r['away_score'];

        $table[$ht]['played']++; $table[$at]['played']++;
        $table[$ht]['gf'] += $hs; $table[$ht]['ga'] += $as;
        $table[$at]['gf'] += $as; $table[$at]['ga'] += $hs;

        if ($hs > $as) {
            $table[$ht]['win']++; $table[$ht]['pts'] += 3; $table[$at]['loss']++;
        } elseif ($hs < $as) {
            $table[$at]['win']++; $table[$at]['pts'] += 3; $table[$ht]['loss']++;
        } else {
            $table[$ht]['draw']++; $table[$ht]['pts'] += 1;
            $table[$at]['draw']++; $table[$at]['pts'] += 1;
        }
    }

    // join team names
    $standings = [];
    if ($table) {
        $ids = array_map('intval', array_keys($table));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($table as $tid => $row) {
            $row['team_id'] = (int)$tid;
            $row['team_name'] = $names[$tid] ?? 'Team ' . $tid;
            $standings[] = $row;
        }
        usort($standings, function($a,$b){
            if ($a['pts']==$b['pts']) {
                $gda = $a['gf']-$a['ga']; $gdb = $b['gf']-$b['ga'];
                if ($gda === $gdb) return $b['gf'] - $a['gf'];
                return $gdb - $gda;
            }
            return $b['pts'] - $a['pts'];
        });
    }

    // CSV headers & output
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="standings_sport_'.$sport_id.'.csv"');
    $out = fopen('php://output','w');
    fputcsv($out, ['Position','Team','Played','Won','Draw','Lost','GF','GA','GD','Points']);
    $pos = 1;
    foreach ($standings as $s) {
        fputcsv($out, [
            $pos++,
            $s['team_name'],
            $s['played'],
            $s['win'],
            $s['draw'],
            $s['loss'],
            $s['gf'],
            $s['ga'],
            $s['gf'] - $s['ga'],
            $s['pts']
        ]);
    }
    fclose($out);
    exit;
}

// --- Build standings for page view ---
$sports = $pdo->query('SELECT * FROM sports ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
$standings = []; $matchesCount = 0;
$h2h = []; // head-to-head map: h2h[tid][other_tid] = array of match rows
if ($sport_id) {
    // finished matches with results
    $stmt = $pdo->prepare('
        SELECT m.id, m.home_team, m.away_team, m.match_date, r.home_score, r.away_score
        FROM matches m
        JOIN results r ON r.match_id = m.id
        WHERE m.sport_id = ?
        ORDER BY r.recorded_at ASC, m.match_date ASC
    ');
    $stmt->execute([$sport_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $matchesCount = count($rows);

    $table = [];
    foreach ($rows as $r) {
        foreach (['home_team','away_team'] as $tkey) {
            if (!isset($table[$r[$tkey]])) $table[$r[$tkey]]=['played'=>0,'win'=>0,'draw'=>0,'loss'=>0,'gf'=>0,'ga'=>0,'pts'=>0];
        }
        $ht=(int)$r['home_team']; $at=(int)$r['away_team'];
        $hs=(int)$r['home_score']; $as=(int)$r['away_score'];

        // populate head-to-head records
        $h2h[$ht][$at][] = $r;
        $h2h[$at][$ht][] = $r;

        $table[$ht]['played']++; $table[$at]['played']++;
        $table[$ht]['gf'] += $hs; $table[$ht]['ga'] += $as;
        $table[$at]['gf'] += $as; $table[$at]['ga'] += $hs;

        if ($hs > $as) {
            $table[$ht]['win']++; $table[$ht]['pts'] += 3; $table[$at]['loss']++;
        } elseif ($hs < $as) {
            $table[$at]['win']++; $table[$at]['pts'] += 3; $table[$ht]['loss']++;
        } else {
            $table[$ht]['draw']++; $table[$ht]['pts'] += 1;
            $table[$at]['draw']++; $table[$at]['pts'] += 1;
        }
    }

    if ($table) {
        $ids = array_map('intval', array_keys($table));
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $pdo->prepare("SELECT id, name FROM teams WHERE id IN ($placeholders)");
        $stmt->execute($ids);
        $names = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($table as $tid=>$row) {
            $row['team_id'] = (int)$tid;
            $row['team_name'] = $names[$tid] ?? 'Team '.$tid;
            $standings[] = $row;
        }

        usort($standings,function($a,$b){
            if ($a['pts']==$b['pts']) {
                $gda = $a['gf']-$a['ga']; $gdb = $b['gf']-$b['ga'];
                if ($gda === $gdb) return $b['gf'] - $a['gf'];
                return $gdb - $gda;
            }
            return $b['pts'] - $a['pts'];
        });
    }
}

// Build ties map: team_id => list of other team_ids tied on points
$ties = [];
if ($standings) {
    // group by points
    $byPts = [];
    foreach ($standings as $s) $byPts[$s['pts']][] = $s['team_id'];
    foreach ($byPts as $pts => $group) {
        if (count($group) > 1) {
            foreach ($group as $tid) {
                $ties[$tid] = array_values(array_diff($group, [$tid]));
            }
        }
    }
}

// Recent 5 completed matches for this sport (by recorded_at desc)
$recent = [];
if ($sport_id) {
    $stmt = $pdo->prepare('
        SELECT r.*, m.match_date, t1.name AS home_name, t2.name AS away_name
        FROM results r
        JOIN matches m ON m.id = r.match_id
        JOIN teams t1 ON t1.id = m.home_team
        JOIN teams t2 ON t2.id = m.away_team
        WHERE m.sport_id = ?
        ORDER BY r.recorded_at DESC
        LIMIT 5
    ');
    $stmt->execute([$sport_id]);
    $recent = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// JSON-encode h2h and ties for client usage
$h2h_json = json_encode($h2h);
$ties_json = json_encode($ties);
$standings_for_js = json_encode(array_map(function($s){ return ['team_id'=>$s['team_id'],'team_name'=>$s['team_name'],'pts'=>$s['pts'],'gf'=>$s['gf'],'ga'=>$s['ga']]; }, $standings));
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Standings — Mayoral Cup Admin</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap');

body {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #f8f9fa 100%);
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
    max-width: 1200px;
}

.navbar {
    background: linear-gradient(90deg, #FFD700, #32CD32, #FF4500, #1E90FF);
    padding: 1rem 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    position: relative;
    z-index: 10;
    margin-bottom: 2rem;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white !important;
    font-weight: 700;
    font-size: 1.3rem;
}

.navbar-brand img {
    height: 50px;
    width: auto;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.btn-back {
    background: white;
    color: #FF4500;
    font-weight: 600;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-back:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    color: #FF4500;
}

.page-header h3 {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #6c757d;
    margin: 0;
}

.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    margin-bottom: 2rem;
}

.card-header-custom {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1.5rem;
}

.card-header-custom i {
    font-size: 1.5rem;
}

.sport-selector-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    transition: all 0.3s ease;
    font-weight: 500;
}

.form-select:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
}

.btn-export {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    border: none;
    padding: 0.65rem 1.5rem;
    font-weight: 600;
    border-radius: 10px;
    color: white;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(30, 144, 255, 0.3);
    color: white;
}

.btn-view {
    background: white;
    border: 2px solid #e9ecef;
    padding: 0.5rem 1rem;
    font-weight: 600;
    border-radius: 10px;
    color: #2c3e50;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-view:hover {
    border-color: #FFD700;
    background: #fffbf0;
    color: #2c3e50;
    transform: translateY(-2px);
}

.stats-badge {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 0.5rem 1rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #2c3e50;
}

.table-responsive {
    border-radius: 12px;
    overflow: hidden;
}

.table {
    margin-bottom: 0;
}

.table thead th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    color: #2c3e50;
    font-weight: 700;
    border: none;
    padding: 1rem 0.75rem;
    font-size: 0.9rem;
    text-align: center;
}

.table thead th:first-child,
.table thead th:nth-child(2) {
    text-align: left;
}

.table tbody td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-color: #f1f3f5;
    color: #2c3e50;
    text-align: center;
}

.table tbody td:first-child,
.table tbody td:nth-child(2) {
    text-align: left;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.position-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
}

.position-1 {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: white;
}

.position-2 {
    background: linear-gradient(135deg, #C0C0C0 0%, #A9A9A9 100%);
    color: white;
}

.position-3 {
    background: linear-gradient(135deg, #CD7F32 0%, #B8733F 100%);
    color: white;
}

.position-other {
    background: #f1f3f5;
    color: #2c3e50;
}

.team-name {
    font-weight: 600;
    color: #2c3e50;
}

.h2h-icon {
    cursor: pointer;
    color: #1E90FF;
    margin-left: 0.5rem;
    transition: all 0.2s ease;
}

.h2h-icon:hover {
    color: #0066CC;
    transform: scale(1.2);
}

.gd-positive {
    color: #32CD32;
    font-weight: 700;
}

.gd-negative {
    color: #FF4500;
    font-weight: 700;
}

.gd-neutral {
    color: #6c757d;
    font-weight: 600;
}

.points-badge {
    background: linear-gradient(135deg, #32CD32 0%, #228B22 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
}

.recent-match-item {
    background: white;
    border: 2px solid #f1f3f5;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    transition: all 0.2s ease;
}

.recent-match-item:hover {
    border-color: #FFD700;
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.match-score {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
}

.match-teams {
    font-weight: 600;
    color: #2c3e50;
}

.match-date {
    color: #6c757d;
    font-size: 0.9rem;
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

.modal-content {
    border: none;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
}

.modal-header {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: none;
    border-radius: 16px 16px 0 0;
    padding: 1.5rem;
}

.modal-title {
    font-weight: 700;
    color: #2c3e50;
}

.modal-body {
    padding: 1.5rem;
}

.h2h-match-card {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: 0.75rem;
}

.h2h-section-title {
    font-weight: 700;
    color: #2c3e50;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e9ecef;
}
</style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php">
      <img src="<?= $logo_url ?>" alt="Logo">
      <span>Mayoral Cup Admin</span>
    </a>
  </div>
</nav>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="page-header">
          <h3><i class="bi bi-trophy me-2" style="color: #FFD700;"></i>Tournament Standings</h3>
          <p>View group standings and points table by sporting code</p>
      </div>
      <a href="dashboard.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
  </div>

  <!-- Sport Selector -->
  <div class="card p-4">
      <div class="row align-items-end g-3">
          <div class="col-md-6">
              <label class="form-label fw-bold">
                  <i class="bi bi-funnel me-2" style="color: #1E90FF;"></i>Select Sporting Code
              </label>
              <form method="get" id="sportForm">
                  <select name="sport_id" onchange="this.form.submit()" class="form-select">
                      <option value="">— Choose sporting code —</option>
                      <?php foreach ($sports as $s): ?>
                          <option value="<?= (int)$s['id'] ?>" <?= ($sport_id === (int)$s['id']) ? 'selected' : '' ?>>
                              <?= e($s['name']) ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </form>
          </div>
          
          <?php if ($sport_id): ?>
          <div class="col-md-6 text-end">
              <div class="d-flex gap-2 justify-content-end flex-wrap">
                  <a href="fixtures.php?sport_id=<?= (int)$sport_id ?>" class="btn-view">
                      <i class="bi bi-calendar3"></i> View Fixtures
                  </a>
                  <a href="results.php" class="btn-view">
                      <i class="bi bi-clipboard-check"></i> Enter Results
                  </a>
                  <form method="get" class="d-inline">
                      <input type="hidden" name="sport_id" value="<?= (int)$sport_id ?>">
                      <input type="hidden" name="export" value="1">
                      <input type="hidden" name="_csrf" value="<?= e($_SESSION['_csrf']) ?>">
                      <button class="btn-export">
                          <i class="bi bi-download"></i> Export CSV
                      </button>
                  </form>
              </div>
          </div>
          <?php endif; ?>
      </div>
  </div>

  <?php if (!$sport_id): ?>
    <div class="card p-5">
        <div class="empty-state">
            <i class="bi bi-trophy"></i>
            <h5 class="mt-3 mb-2">No Sporting Code Selected</h5>
            <p class="text-muted">Please choose a sporting code from the dropdown above to view standings and recent results.</p>
        </div>
    </div>
  <?php else: ?>
    <?php
    $sname = '';
    foreach ($sports as $s) if ((int)$s['id'] === $sport_id) { $sname = $s['name']; break; }
    ?>
    
    <!-- Sport Info -->
    <div class="card p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h5 class="mb-2">
                    <i class="bi bi-flag-fill me-2" style="color: #32CD32;"></i>
                    <?= e($sname) ?>
                </h5>
                <span class="stats-badge">
                    <i class="bi bi-check-circle-fill" style="color: #32CD32;"></i>
                    <?= (int)$matchesCount ?> matches completed
                </span>
            </div>
        </div>
    </div>

    <?php if (empty($standings)): ?>
        <div class="card p-5">
            <div class="empty-state">
                <i class="bi bi-clipboard-x"></i>
                <h5 class="mt-3 mb-2">No Results Available</h5>
                <p class="text-muted mb-4">No finished matches found for this sport. Once results are entered, the standings will appear here.</p>
                <a href="results.php" class="btn-view">
                    <i class="bi bi-plus-circle"></i> Enter First Result
                </a>
            </div>
        </div>
    <?php else: ?>
        <!-- Standings Table -->
        <div class="card p-4">
            <div class="card-header-custom">
                <i class="bi bi-list-ol" style="color: #FFD700;"></i>
                League Standings
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Team</th>
                            <th style="width: 70px;">P</th>
                            <th style="width: 70px;">W</th>
                            <th style="width: 70px;">D</th>
                            <th style="width: 70px;">L</th>
                            <th style="width: 70px;">GF</th>
                            <th style="width: 70px;">GA</th>
                            <th style="width: 90px;">GD</th>
                            <th style="width: 90px;">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i=1; foreach ($standings as $s):
                            $gd = $s['gf'] - $s['ga'];
                            $gdClass = $gd > 0 ? 'gd-positive' : ($gd < 0 ? 'gd-negative' : 'gd-neutral');
                            $tid = (int)$s['team_id'];
                            
                            $posClass = 'position-other';
                            if ($i === 1) $posClass = 'position-1';
                            elseif ($i === 2) $posClass = 'position-2';
                            elseif ($i === 3) $posClass = 'position-3';
                        ?>
                        <tr>
                            <td>
                                <div class="position-badge <?= $posClass ?>">
                                    <?= $i++ ?>
                                </div>
                            </td>
                            <td>
                                <span class="team-name"><?= e($s['team_name']) ?></span>
                                <?php if (!empty($ties[$tid])): ?>
                                    <i class="bi bi-search h2h-icon" title="View head-to-head vs tied teams" data-team="<?= $tid ?>"></i>
                                <?php endif; ?>
                            </td>
                            <td><?= (int)$s['played'] ?></td>
                            <td><?= (int)$s['win'] ?></td>
                            <td><?= (int)$s['draw'] ?></td>
                            <td><?= (int)$s['loss'] ?></td>
                            <td><?= (int)$s['gf'] ?></td>
                            <td><?= (int)$s['ga'] ?></td>
                            <td class="<?= $gdClass ?>"><?= ($gd > 0 ? '+' : '') . $gd ?></td>
                            <td>
                                <span class="points-badge"><?= (int)$s['pts'] ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Results -->
        <div class="card p-4">
            <div class="card-header-custom">
                <i class="bi bi-clock-history" style="color: #1E90FF;"></i>
                Recent Results
            </div>
            <div class="row">
                <?php if (empty($recent)): ?>
                    <div class="col-12">
                        <p class="text-muted text-center py-3">No recent results recorded.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent as $m): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="recent-match-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="match-date">
                                        <i class="bi bi-calendar-event me-1"></i>
                                        <?= date('d M Y', strtotime($m['match_date'])) ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="text-end flex-grow-1">
                                        <div class="match-teams"><?= e($m['home_name']) ?></div>
                                    </div>
                                    <div class="mx-3">
                                        <span class="match-score"><?= (int)$m['home_score'] ?> - <?= (int)$m['away_score'] ?></span>
                                    </div>
                                    <div class="text-start flex-grow-1">
                                        <div class="match-teams"><?= e($m['away_name']) ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<!-- H2H Modal -->
<div class="modal fade" id="h2hModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-arrow-left-right me-2"></i>Head-to-Head Comparison</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="h2hBody">
        <!-- Content injected by JS -->
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const h2hData = <?= $h2h_json ?>;
const tiesData = <?= $ties_json ?>;
const standings = <?= $standings_for_js ?>;

document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('h2hModal'));
    const body = document.getElementById('h2hBody');

    document.querySelectorAll('.h2h-icon').forEach(icon => {
        icon.addEventListener('click', function() {
            const teamId = parseInt(this.dataset.team);
            const tiedWith = tiesData[teamId] || [];
            const teamInfo = standings.find(s => s.team_id === teamId);
            
            let html = `<div class="mb-4">
                <h6>Tied Teams on ${teamInfo.pts} Points:</h6>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="badge bg-primary p-2">${teamInfo.team_name}</span>
                    ${tiedWith.map(tid => {
                        const t = standings.find(s => s.team_id === tid);
                        return `<span class="badge bg-secondary p-2">${t ? t.team_name : 'Team ' + tid}</span>`;
                    }).join('')}
                </div>
            </div>`;

            tiedWith.forEach(otherId => {
                const otherTeam = standings.find(s => s.team_id === otherId);
                const matches = (h2hData[teamId] && h2hData[teamId][otherId]) ? h2hData[teamId][otherId] : [];
                
                html += `<div class="h2h-section-title">
                    vs ${otherTeam ? otherTeam.team_name : 'Team ' + otherId}
                </div>`;

                if (matches.length === 0) {
                    html += `<p class="text-muted small">No head-to-head matches found.</p>`;
                } else {
                    matches.forEach(m => {
                        const isHome = parseInt(m.home_team) === teamId;
                        const teamScore = isHome ? m.home_score : m.away_score;
                        const otherScore = isHome ? m.away_score : m.home_score;
                        const resultText = teamScore > otherScore ? 'WON' : (teamScore < otherScore ? 'LOST' : 'DRAW');
                        const resultClass = teamScore > otherScore ? 'text-success' : (teamScore < otherScore ? 'text-danger' : 'text-warning');

                        html += `<div class="h2h-match-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted d-block mb-1">${new Date(m.match_date).toLocaleDateString('en-GB', {day:'numeric', month:'short', year:'numeric'})}</small>
                                    <span class="fw-bold">${isHome ? teamInfo.team_name : otherTeam.team_name}</span>
                                    <span class="mx-2">${m.home_score} - ${m.away_score}</span>
                                    <span class="fw-bold">${isHome ? otherTeam.team_name : teamInfo.team_name}</span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold ${resultClass}">${resultText}</span>
                                </div>
                            </div>
                        </div>`;
                    });
                }
            });

            body.innerHTML = html;
            modal.show();
        });
    });
});
</script>
</body>
</html>