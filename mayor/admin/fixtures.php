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

// --- Security helper ---
function e(?string $v): string {
    return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8');
}

// --- CSRF helpers ---
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}
function csrf_field(): string {
    return '<input type="hidden" name="_csrf" value="'.e($_SESSION['_csrf']).'">';
}
function check_csrf(): bool {
    return isset($_POST['_csrf'], $_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $_POST['_csrf']);
}

// --- Handle Delete All Fixtures ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_all' && check_csrf()) {
    $pdo->exec("DELETE FROM matches");
    $pdo->exec("DELETE FROM results");
    $message = "✅ All fixtures and results cleared.";
}

// --- Round Robin Generator ---
function round_robin_schedule(array $teams, string $startDate, bool $doubleRound = false): array {
    $fixtures = [];
    $numTeams = count($teams);
    if ($numTeams < 2) return $fixtures;

    $isOdd = $numTeams % 2 !== 0;
    if ($isOdd) { $teams[] = null; $numTeams++; }

    $rounds = $numTeams - 1;
    $half = $numTeams / 2;
    $currentDate = new DateTime($startDate);
    $venues = ["Main Field", "Court 1", "Court 2", "Stadium A", "Arena B"];
    $vIndex = 0;

    for ($round = 0; $round < $rounds; $round++) {
        for ($i = 0; $i < $half; $i++) {
            $home = $teams[$i];
            $away = $teams[$numTeams - 1 - $i];
            if ($home === null || $away === null || $home === $away) continue;

            $fixtures[] = [
                'home'  => $home,
                'away'  => $away,
                'date'  => $currentDate->format('Y-m-d H:i:s'),
                'venue' => $venues[$vIndex % count($venues)]
            ];
            $vIndex++;
        }

        // rotate
        $pivot = array_shift($teams);
        $moved = array_pop($teams);
        array_unshift($teams, $pivot);
        array_splice($teams, 1, 0, [$moved]);

        $currentDate->modify('+1 day');
    }

    if ($doubleRound) {
        $reverse = [];
        foreach ($fixtures as $f) {
            $reverse[] = [
                'home'  => $f['away'],
                'away'  => $f['home'],
                'date'  => (new DateTime($f['date']))->modify('+14 days')->format('Y-m-d H:i:s'),
                'venue' => $f['venue']
            ];
        }
        $fixtures = array_merge($fixtures, $reverse);
    }

    return $fixtures;
}

// --- Handle Generate Fixtures ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'generate' && check_csrf()) {
    $sport_id   = (int)($_POST['sport_id'] ?? 0);
    $round_type = $_POST['round_type'] ?? 'single';
    $start_date = $_POST['start_date'] ?? date('Y-m-d 10:00:00');

    if ($sport_id > 0) {
        // clear current fixtures before generating new ones
        $pdo->exec("DELETE FROM matches");
        $pdo->exec("DELETE FROM results");

        $teamsStmt = $pdo->prepare("SELECT id FROM teams WHERE sport_id = ?");
        $teamsStmt->execute([$sport_id]);
        $teams = $teamsStmt->fetchAll(PDO::FETCH_COLUMN);

        if (count($teams) >= 2) {
            $fixtures = round_robin_schedule($teams, $start_date, $round_type === 'double');
            $insert = $pdo->prepare("INSERT INTO matches (sport_id, home_team, away_team, match_date, venue, status)
                                     VALUES (?, ?, ?, ?, ?, 'scheduled')");
            foreach ($fixtures as $f) {
                $insert->execute([$sport_id, $f['home'], $f['away'], $f['date'], $f['venue']]);
            }
            $message = "✅ Fixtures cleared and regenerated successfully.";
        } else {
            $message = "⚠️ Not enough teams for that sport to generate fixtures.";
        }
    } else {
        $message = "⚠️ Please select a sport.";
    }
}

// --- Handle Save/Update Fixture ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_fixture' && check_csrf()) {
    $id = (int)($_POST['id'] ?? 0);
    $venue = trim($_POST['venue'] ?? '');
    $date = trim($_POST['match_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE matches SET venue=?, match_date=?, status=? WHERE id=?");
        $stmt->execute([$venue, $date, $status, $id]);
        $message = "💾 Fixture #$id updated successfully.";
    }
}

// --- Fetch data for page ---
$sports = $pdo->query("SELECT * FROM sports ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$matches = $pdo->query("
    SELECT m.*, s.name AS sport, t1.name AS home_name, t2.name AS away_name
    FROM matches m
    JOIN sports s ON s.id = m.sport_id
    JOIN teams t1 ON t1.id = m.home_team
    JOIN teams t2 ON t2.id = m.away_team
    ORDER BY m.match_date
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Fixtures Management — Mayoral Cup Admin</title>
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
    max-width: 1400px;
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

.alert-warning {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
    color: #856404;
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

.form-control-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
}

.form-select-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
}

.form-check-input:checked {
    background-color: #FFD700;
    border-color: #FFD700;
}

.form-check-label {
    font-weight: 500;
    color: #2c3e50;
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

.btn-save {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    border: none;
    padding: 0.5rem 1rem;
    font-weight: 600;
    border-radius: 8px;
    color: white;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
}

.btn-save:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(30, 144, 255, 0.3);
    color: white;
}

.teams-list-box {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 10px;
    padding: 1rem;
    margin-top: 0.5rem;
    min-height: 50px;
    display: flex;
    align-items: center;
}

.teams-list-box .text-muted {
    color: #6c757d !important;
    font-size: 0.95rem;
}

.teams-list-box strong {
    color: #2c3e50;
    font-weight: 700;
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

.badge-sport {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
}

.badge-status {
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
}

.badge-scheduled {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    color: white;
}

.badge-live {
    background: linear-gradient(135deg, #FF4500 0%, #DC143C 100%);
    color: white;
}

.badge-completed {
    background: linear-gradient(135deg, #32CD32 0%, #228B22 100%);
    color: white;
}

.badge-postponed {
    background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
    color: white;
}

.badge-cancelled {
    background: linear-gradient(135deg, #808080 0%, #696969 100%);
    color: white;
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
          <h3><i class="bi bi-calendar-event me-2" style="color: #1E90FF;"></i>Manage Fixtures</h3>
          <p>Generate and manage tournament fixtures and schedules</p>
      </div>
      <a href="dashboard.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
  </div>

  <?php if (!empty($message)): ?>
  <div class="alert alert-custom <?= strpos($message, '⚠️') !== false ? 'alert-warning' : 'alert-success' ?> mb-4">
      <?= e($message) ?>
  </div>
  <?php endif; ?>

  <!-- Delete All Fixtures -->
  <div class="card p-4 mb-4">
      <div class="card-header-custom">
          <i class="bi bi-trash-fill" style="color: #FF4500;"></i>
          Clear All Fixtures
      </div>
      <p class="text-muted mb-3">Warning: This will permanently delete all fixtures and results from the database.</p>
      <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete_all">
          <button class="btn-danger-custom" onclick="return confirm('Are you sure you want to delete ALL fixtures and results? This action cannot be undone.')">
              <i class="bi bi-exclamation-triangle"></i>
              Clear All Fixtures
          </button>
      </form>
  </div>

  <!-- Generate Fixtures -->
  <div class="card p-4 mb-4">
      <div class="card-header-custom">
          <i class="bi bi-gear-fill" style="color: #32CD32;"></i>
          Generate Fixtures
      </div>
      <form method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="generate">

          <div class="mb-3">
              <label class="form-label">Select Sporting Code</label>
              <select name="sport_id" id="sportSelect" class="form-select" required>
                  <option value="">Choose sport...</option>
                  <?php foreach ($sports as $sport): ?>
                      <option value="<?= e((string)$sport['id']) ?>"><?= e($sport['name']) ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div id="teamsList" class="teams-list-box">
              <span class="text-muted">Select a sport to view registered teams</span>
          </div>

          <div class="row g-3 mt-3">
              <div class="col-md-6">
                  <label class="form-label">Fixture Type</label>
                  <div class="form-check">
                      <input class="form-check-input" type="radio" name="round_type" value="single" id="single" checked>
                      <label class="form-check-label" for="single">
                          Single Round Robin (Each team plays others once)
                      </label>
                  </div>
                  <div class="form-check mt-2">
                      <input class="form-check-input" type="radio" name="round_type" value="double" id="double">
                      <label class="form-check-label" for="double">
                          Double Round Robin (Home & Away)
                      </label>
                  </div>
              </div>

              <div class="col-md-6">
                  <label class="form-label">Start Date & Time</label>
                  <input type="datetime-local" name="start_date" class="form-control" value="<?= e(date('Y-m-d\TH:i')) ?>" required>
                  <div class="form-text">Fixtures will be scheduled starting from this date</div>
              </div>
          </div>

          <div class="text-end mt-4">
              <button class="btn-submit">
                  <i class="bi bi-lightning-charge"></i>
                  Generate Fixtures
              </button>
          </div>
      </form>
  </div>

  <!-- Fixtures Table -->
  <div class="card p-4">
      <div class="card-header-custom">
          <i class="bi bi-list-ul" style="color: #1E90FF;"></i>
          Existing Fixtures
      </div>
      <div class="table-responsive">
          <table class="table">
              <thead>
                  <tr>
                      <th>Date & Time</th>
                      <th>Sport</th>
                      <th>Fixture</th>
                      <th>Venue</th>
                      <th>Status</th>
                      <th style="width: 100px;">Action</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach ($matches as $m): ?>
                  <tr>
                      <form method="post">
                          <?= csrf_field() ?>
                          <input type="hidden" name="action" value="save_fixture">
                          <input type="hidden" name="id" value="<?= e((string)$m['id']) ?>">

                          <td>
                              <input type="datetime-local" class="form-control form-control-sm" name="match_date" 
                                     value="<?= e(date('Y-m-d\TH:i', strtotime($m['match_date']))) ?>" required>
                          </td>
                          <td><span class="badge-sport"><?= e($m['sport']) ?></span></td>
                          <td>
                              <strong><?= e($m['home_name'] ?? '') ?></strong> vs <strong><?= e($m['away_name'] ?? '') ?></strong>
                          </td>
                          <td>
                              <input type="text" name="venue" class="form-control form-control-sm" 
                                     value="<?= e($m['venue']) ?>" placeholder="Enter venue">
                          </td>
                          <td>
                              <select name="status" class="form-select form-select-sm">
                                  <?php
                                  $statuses = ['scheduled', 'postponed', 'cancelled', 'live', 'completed'];
                                  foreach ($statuses as $s) {
                                      $sel = ($m['status'] === $s) ? 'selected' : '';
                                      echo "<option value='".e($s)."' $sel>".ucfirst($s)."</option>";
                                  }
                                  ?>
                              </select>
                          </td>
                          <td>
                              <button class="btn-save">
                                  <i class="bi bi-save"></i> Save
                              </button>
                          </td>
                      </form>
                  </tr>
              <?php endforeach; ?>
              <?php if (empty($matches)): ?>
                  <tr>
                      <td colspan="6">
                          <div class="empty-state">
                              <i class="bi bi-calendar-x"></i>
                              <p>No fixtures found. Generate fixtures using the form above.</p>
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
document.getElementById('sportSelect').addEventListener('change', async function() {
    const id = this.value;
    const div = document.getElementById('teamsList');
    if (!id) { 
        div.innerHTML = '<span class="text-muted">Select a sport to view registered teams</span>'; 
        return; 
    }
    
    div.innerHTML = '<span class="text-muted">Loading teams...</span>';
    
    try {
        const res = await fetch('get_teams.php?sport_id=' + id);
        const teams = await res.json();
        if (teams.length === 0) {
            div.innerHTML = '<span style="color: #FF4500; font-weight: 600;">⚠️ No teams registered for this sport.</span>';
        } else {
            div.innerHTML = '<div><strong>Registered Teams (' + teams.length + '):</strong> <span style="color: #2c3e50;">' + 
                            teams.map(t => e(t.name)).join(', ') + '</span></div>';
        }
    } catch (error) {
        div.innerHTML = '<span class="text-danger">Error loading teams</span>';
    }
});

function e(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}
</script>
</body>
</html>