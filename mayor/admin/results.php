<?php
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/db.php';
require_once __DIR__.'/../inc/auth.php';
require_admin();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

if (session_status() === PHP_SESSION_NONE) session_start();

// Include sport-specific helpers
if (file_exists(__DIR__ . '/../inc/netball.php')) require_once __DIR__ . '/../inc/netball.php';

// --- CSRF helpers ---
if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
function csrf_field(): string { return '<input type="hidden" name="_csrf_token" value="'.htmlspecialchars($_SESSION['_csrf']).'">'; }
function check_csrf(): bool { return isset($_POST['_csrf_token'], $_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $_POST['_csrf_token']); }

$error = null;
$message = null;

// --- Handle save result ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'saveresult') {
    if (!check_csrf()) $error = 'Invalid CSRF token.';
    else {
        $match_id   = (int)($_POST['match_id'] ?? 0);
        $home_score = max(0,(int)($_POST['home_score'] ?? 0));
        $away_score = max(0,(int)($_POST['away_score'] ?? 0));
        $details_arr = $_POST['details_arr'] ?? [];

        // Check match exists and is not in the future
        $stmt = $pdo->prepare("SELECT match_date, sport_id FROM matches WHERE id = ?");
        $stmt->execute([$match_id]);
        $match = $stmt->fetch();
        if (!$match) $error = 'Match not found.';
        elseif (strtotime($match['match_date']) > time()) $error = 'Cannot record results for a future match.';
        else {
            // Build details JSON if sport helper exists
            $details = function_exists('build_netball_details') ? build_netball_details($details_arr) : json_encode($details_arr);
            try {
                $pdo->prepare('INSERT INTO results (match_id, home_score, away_score, details, recorded_by) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$match_id, $home_score, $away_score, $details, $_SESSION['user']['id']]);
                $pdo->prepare('UPDATE matches SET status = "finished" WHERE id = ?')->execute([$match_id]);
                $message = '✅ Result recorded successfully.';
            } catch (PDOException $e) {
                $error = 'Database error: ' . htmlspecialchars($e->getMessage());
            }
        }
    }
}

// --- Handle archive / clear ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'archive_results') {
    if (!check_csrf()) $error = 'Invalid CSRF token.';
    else {
        $pdo->exec('UPDATE results SET archived = 1');
        $message = '✅ All previous results archived successfully.';
    }
}

// --- Fetch matches eligible for result entry ---
$matches = $pdo->query('SELECT m.*, s.name AS sport, t1.name AS home_name, t2.name AS away_name, s.id AS sport_id
    FROM matches m
    JOIN sports s ON s.id = m.sport_id
    JOIN teams t1 ON t1.id = m.home_team
    JOIN teams t2 ON t2.id = m.away_team
    WHERE m.status IN ("scheduled","live")
    ORDER BY m.match_date')->fetchAll();

// --- Fetch recent results ---
$results = $pdo->query('SELECT r.*, m.*, t1.name AS home_name, t2.name AS away_name 
    FROM results r
    JOIN matches m ON m.id = r.match_id
    JOIN teams t1 ON t1.id = m.home_team
    JOIN teams t2 ON t2.id = m.away_team
    WHERE r.archived = 0
    ORDER BY r.recorded_at DESC
    LIMIT 40')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Results Management — Mayoral Cup Admin</title>
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

.alert-custom {
    border: none;
    border-radius: 12px;
    padding: 1rem 1.5rem;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.alert-danger {
    background: linear-gradient(135deg, #f8d7da 0%, #f5c2c7 100%);
    color: #842029;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
}

.sport-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 1.5rem;
    border-radius: 12px;
    margin-top: 1rem;
}

.sport-section strong {
    color: #2c3e50;
    font-size: 1.1rem;
}

.btn-submit {
    background: linear-gradient(135deg, #32CD32 0%, #228B22 100%);
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 700;
    border-radius: 10px;
    color: white;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(50, 205, 50, 0.3);
    color: white;
}

.btn-danger-custom {
    background: linear-gradient(135deg, #FF4500 0%, #DC143C 100%);
    border: none;
    padding: 0.75rem 2rem;
    font-weight: 700;
    border-radius: 10px;
    color: white;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-danger-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 69, 0, 0.3);
    color: white;
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
}

.table tbody td {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-color: #f1f3f5;
    color: #2c3e50;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.result-score {
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
}

.result-teams {
    font-weight: 600;
}

.result-date {
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
          <h3><i class="bi bi-clipboard-check me-2" style="color: #32CD32;"></i>Record Match Results</h3>
          <p>Enter and manage match results and scores</p>
      </div>
      <a href="dashboard.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
  </div>

  <?php if($error): ?>
  <div class="alert alert-custom alert-danger mb-4">
      <i class="bi bi-exclamation-triangle me-2"></i><?=htmlspecialchars($error)?>
  </div>
  <?php endif; ?>
  
  <?php if($message): ?>
  <div class="alert alert-custom alert-success mb-4">
      <i class="bi bi-check-circle me-2"></i><?=htmlspecialchars($message)?>
  </div>
  <?php endif; ?>

  <!-- Record Result Form -->
  <div class="card p-4 mb-4">
      <div class="card-header-custom">
          <i class="bi bi-plus-circle-fill" style="color: #32CD32;"></i>
          Enter New Result
      </div>
      <form method="post">
          <input type="hidden" name="action" value="saveresult">
          <?=csrf_field()?>

          <div class="mb-3">
              <label class="form-label">Select Match</label>
              <select id="matchSelect" name="match_id" class="form-select" required>
                  <option value="">Choose match...</option>
                  <?php foreach($matches as $m): ?>
                  <option value="<?=$m['id']?>" data-sport-id="<?=$m['sport_id']?>">
                      <?=htmlspecialchars("{$m['sport']} • {$m['home_name']} vs {$m['away_name']} • ".date('d M H:i', strtotime($m['match_date'])))?>
                  </option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="row g-3 mb-3">
              <div class="col-md-6">
                  <label class="form-label">Home Score</label>
                  <input name="home_score" type="number" class="form-control" placeholder="Enter home score" min="0" required>
              </div>
              <div class="col-md-6">
                  <label class="form-label">Away Score</label>
                  <input name="away_score" type="number" class="form-control" placeholder="Enter away score" min="0" required>
              </div>
          </div>

          <!-- Sport-specific sections -->
          <div id="sportSections">
              <!-- Netball example -->
              <div class="sport-section" data-sport-id="2" style="display:none;">
                  <strong><i class="bi bi-trophy me-2" style="color: #FFD700;"></i>Netball: Quarter Scores</strong>
                  <div class="row g-2 mt-3">
                      <?php for($i=0;$i<4;$i++): ?>
                      <div class="col-12">
                          <label class="form-label">Quarter <?=($i+1)?></label>
                          <div class="row g-2">
                              <div class="col">
                                  <input name="details_arr[quarters][<?=$i?>][home]" type="number" class="form-control" min="0" placeholder="Home">
                              </div>
                              <div class="col">
                                  <input name="details_arr[quarters][<?=$i?>][away]" type="number" class="form-control" min="0" placeholder="Away">
                              </div>
                          </div>
                      </div>
                      <?php endfor; ?>
                  </div>
                  <div class="mt-3">
                      <label class="form-label">Match Notes</label>
                      <textarea name="details_arr[notes]" class="form-control" rows="3" placeholder="e.g. MVP: #7, Best Player: John Smith"></textarea>
                  </div>
              </div>
          </div>

          <div class="text-end mt-4">
              <button class="btn-submit">
                  <i class="bi bi-check2-circle"></i>
                  Save Result
              </button>
          </div>
      </form>
  </div>

  <!-- Archive Button -->
  <div class="card p-4 mb-4">
      <div class="card-header-custom">
          <i class="bi bi-archive-fill" style="color: #FF4500;"></i>
          Archive Previous Results
      </div>
      <p class="text-muted mb-3">Archive all previous results to clear the recent results table. Archived results will no longer appear here but remain in the database.</p>
      <form method="post">
          <input type="hidden" name="action" value="archive_results">
          <?=csrf_field()?>
          <button type="submit" class="btn-danger-custom" onclick="return confirm('Archive all previous results? They will no longer appear in the recent results table.')">
              <i class="bi bi-archive"></i>
              Archive All Results
          </button>
      </form>
  </div>

  <!-- Recent Results -->
  <div class="card p-4">
      <div class="card-header-custom">
          <i class="bi bi-clock-history" style="color: #1E90FF;"></i>
          Recent Results
      </div>
      <div class="table-responsive">
          <table class="table">
              <thead>
                  <tr>
                      <th>Match</th>
                      <th style="width: 150px;">Score</th>
                      <th style="width: 180px;">Recorded At</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach($results as $r): ?>
              <tr>
                  <td>
                      <div class="result-teams">
                          <?=htmlspecialchars($r['home_name'])?> vs <?=htmlspecialchars($r['away_name'])?>
                      </div>
                      <div class="result-date">
                          <?=htmlspecialchars(date('d M Y, H:i', strtotime($r['match_date'])))?>
                      </div>
                  </td>
                  <td>
                      <span class="result-score"><?=$r['home_score']?> - <?=$r['away_score']?></span>
                  </td>
                  <td class="result-date">
                      <?=htmlspecialchars(date('d M Y, H:i', strtotime($r['recorded_at'])))?>
                  </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($results)): ?>
              <tr>
                  <td colspan="3">
                      <div class="empty-state">
                          <i class="bi bi-clipboard-x"></i>
                          <p>No results recorded yet. Enter your first result above!</p>
                      </div>
                  </td>
              </tr>
              <?php endif; ?>
              </tbody>
          </table>
      </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Show/hide sport-specific sections dynamically
const matchSelect = document.getElementById('matchSelect');
const sportSections = document.querySelectorAll('.sport-section');
matchSelect.addEventListener('change', function() {
    const sportId = matchSelect.selectedOptions[0]?.dataset.sportId;
    sportSections.forEach(s => s.style.display = (s.dataset.sportId === sportId) ? 'block' : 'none');
});
</script>
</body>
</html>