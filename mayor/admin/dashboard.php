<?php
// Enable full error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/db.php';
require_admin();

$user = current_user();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// Totals
$totals = $pdo->query('
    SELECT 
        (SELECT COUNT(*) FROM teams) as teams, 
        (SELECT COUNT(*) FROM players) as players, 
        (SELECT COUNT(*) FROM matches) as matches
')->fetch();

// Corrected analytics (reflect actual DB)
$upcoming = (int) $pdo->query("
    SELECT COUNT(*) FROM matches 
    WHERE status = 'scheduled' 
      AND match_date > NOW()
")->fetchColumn();

$completed = (int) $pdo->query("
    SELECT COUNT(DISTINCT m.id)
    FROM matches m
    LEFT JOIN results r ON r.match_id = m.id
    WHERE m.status = 'completed' OR r.id IS NOT NULL
")->fetchColumn();

$sports_count = (int) $pdo->query("SELECT COUNT(*) FROM sports")->fetchColumn();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mayoral Cup Admin Dashboard</title>
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
}

.navbar {
    background: linear-gradient(90deg, #FFD700, #32CD32, #FF4500, #1E90FF);
    padding: 1rem 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    position: relative;
    z-index: 10;
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 1rem;
    color: white !important;
    font-weight: 700;
    font-size: 1.4rem;
}

.navbar-brand img {
    height: 60px;
    width: auto;
    border-radius: 50%;
    border: 3px solid white;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}

.user-info {
    color: white;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-logout {
    background: white;
    color: #FF4500;
    font-weight: 600;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 50px;
    transition: all 0.3s ease;
}

.btn-logout:hover {
    background: #fff;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    color: #FF4500;
}

.page-header {
    text-align: center;
    margin: 2rem 0;
}

.page-header h2 {
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.page-header p {
    color: #6c757d;
    font-size: 1.05rem;
}

.dashboard-card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    overflow: hidden;
    height: 100%;
}

.dashboard-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.dashboard-card .icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.9;
}

.card-title {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1rem;
    margin-bottom: 0.5rem;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 1rem 0;
}

.stat-gold { color: #FFD700; }
.stat-green { color: #32CD32; }
.stat-red { color: #FF4500; }

.quick-btn {
    font-weight: 600;
    border-radius: 10px;
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.quick-btn:hover {
    transform: scale(1.05);
}

.section-title {
    font-weight: 700;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    font-size: 1.5rem;
}

.analytics-card {
    border: none;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    padding: 1.5rem;
    text-align: center;
    transition: all 0.3s ease;
}

.analytics-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.12);
}

.analytics-card h6 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.analytics-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.75rem;
}

.analytics-primary { color: #1E90FF; }
.analytics-success { color: #32CD32; }
.analytics-info { color: #FFD700; }

.badge-quick {
    font-size: 0.75rem;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
}

.management-list {
    display: grid;
    gap: 1rem;
}

.management-item {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border: none;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    transition: all 0.3s ease;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 1rem;
    color: #2c3e50;
}

.management-item:hover {
    transform: translateX(8px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    background: rgba(255, 255, 255, 1);
    color: #2c3e50;
}

.management-item i {
    font-size: 1.5rem;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

.management-item .text-primary { background: rgba(30, 144, 255, 0.1); }
.management-item .text-success { background: rgba(50, 205, 50, 0.1); }
.management-item .text-danger { background: rgba(255, 69, 0, 0.1); }
.management-item .text-warning { background: rgba(255, 215, 0, 0.1); }
.management-item .text-info { background: rgba(30, 144, 255, 0.1); }
.management-item .text-secondary { background: rgba(108, 117, 125, 0.1); }

.management-item span {
    font-weight: 600;
    font-size: 1.05rem;
}

.footer {
    margin-top: 4rem;
    padding: 2rem 0;
    text-align: center;
    color: #6c757d;
    font-size: 0.95rem;
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
    <div class="d-flex align-items-center gap-3">
      <span class="user-info">
        <i class="bi bi-person-circle"></i>
        <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>
      </span>
      <a class="btn btn-logout" href="/mayor/logout.php">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="page-header">
    <h2>Tournament Control Panel</h2>
    <p>Manage teams, fixtures, results, and standings with accuracy</p>
  </div>

  <!-- Stats Overview -->
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card dashboard-card text-center p-4">
        <div class="icon stat-gold"><i class="bi bi-people-fill"></i></div>
        <h6 class="card-title">Total Teams</h6>
        <div class="stat-number stat-gold"><?= (int)$totals['teams'] ?></div>
        <a href="teams.php" class="btn btn-warning quick-btn w-100">
          <i class="bi bi-gear-fill"></i> Manage Teams
        </a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card text-center p-4">
        <div class="icon stat-green"><i class="bi bi-person-badge-fill"></i></div>
        <h6 class="card-title">Total Players</h6>
        <div class="stat-number stat-green"><?= (int)$totals['players'] ?></div>
        <a href="players.php" class="btn btn-success quick-btn w-100">
          <i class="bi bi-gear-fill"></i> Manage Players
        </a>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card dashboard-card text-center p-4">
        <div class="icon stat-red"><i class="bi bi-calendar-event-fill"></i></div>
        <h6 class="card-title">Total Matches</h6>
        <div class="stat-number stat-red"><?= (int)$totals['matches'] ?></div>
        <a href="fixtures.php" class="btn btn-danger quick-btn w-100">
          <i class="bi bi-gear-fill"></i> Manage Fixtures
        </a>
      </div>
    </div>
  </div>

  <!-- Analytics Row -->
  <h5 class="section-title">
    <i class="bi bi-graph-up"></i>
    Quick Insights
  </h5>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="analytics-card">
        <h6>
          <i class="bi bi-calendar2-week"></i>
          Upcoming Fixtures
        </h6>
        <div class="analytics-number analytics-primary"><?= $upcoming ?></div>
        <span class="badge bg-primary badge-quick">Scheduled & Future</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="analytics-card">
        <h6>
          <i class="bi bi-check-circle-fill"></i>
          Completed Matches
        </h6>
        <div class="analytics-number analytics-success"><?= $completed ?></div>
        <span class="badge bg-success badge-quick">With Results</span>
      </div>
    </div>
    <div class="col-md-4">
      <div class="analytics-card">
        <h6>
          <i class="bi bi-dribbble"></i>
          Sporting Codes
        </h6>
        <div class="analytics-number analytics-info"><?= $sports_count ?></div>
        <span class="badge bg-warning badge-quick">Active Sports</span>
      </div>
    </div>
  </div>

  <!-- Management Links -->
  <h5 class="section-title">
    <i class="bi bi-list-check"></i>
    Management Modules
  </h5>
  <div class="management-list">
    <a class="management-item" href="teams.php">
      <i class="bi bi-people-fill text-primary"></i>
      <span>Manage Teams</span>
    </a>
    <a class="management-item" href="players.php">
      <i class="bi bi-person-lines-fill text-success"></i>
      <span>Manage Players</span>
    </a>
    <a class="management-item" href="fixtures.php">
      <i class="bi bi-calendar-check-fill text-danger"></i>
      <span>Fixtures & Schedule</span>
    </a>
    <a class="management-item" href="results.php">
      <i class="bi bi-trophy-fill text-warning"></i>
      <span>Enter Results</span>
    </a>
    <a class="management-item" href="standings.php">
      <i class="bi bi-bar-chart-fill text-info"></i>
      <span>View Standings</span>
    </a>
    <a class="management-item" href="settings.php">
      <i class="bi bi-gear-fill text-secondary"></i>
      <span>Tournament Settings</span>
    </a>
  </div>

  <div class="footer">
    <p>© <?= date('Y') ?> Mayoral Cup Tournament Management • Built with ❤️ for sports</p>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>