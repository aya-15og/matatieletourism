<?php
require_once __DIR__ . '/../inc/auth.php';
require_admin();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// Handle saving
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(__DIR__ . '/../data/settings.json', json_encode($_POST, JSON_PRETTY_PRINT));
    $msg = 'Settings saved successfully!';
}

// Load settings
$settings = @json_decode(@file_get_contents(__DIR__ . '/../data/settings.json'), true) ?: [];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Tournament Settings — Mayoral Cup Admin</title>
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
    max-width: 900px;
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

.page-header {
    margin-bottom: 2rem;
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

.alert {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.alert-success {
    background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
    color: #155724;
}

.setting-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.setting-section:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.setting-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: 1rem;
}

.icon-win {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
}

.icon-draw {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
}

.icon-loss {
    background: linear-gradient(135deg, #FF4500 0%, #DC143C 100%);
    color: white;
}

.icon-tie {
    background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);
    color: white;
}

.icon-calendar {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    color: white;
}

.form-label {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 0.65rem 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-control:focus, .form-select:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
    background: white;
}

.form-text {
    color: #6c757d;
    font-size: 0.875rem;
    margin-top: 0.5rem;
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

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

.section-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.section-description {
    color: #6c757d;
    font-size: 0.95rem;
    margin-bottom: 1rem;
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
          <h3><i class="bi bi-gear-fill me-2" style="color: #FFD700;"></i>Tournament Settings</h3>
          <p>Configure scoring rules, tie-breakers, and tournament parameters</p>
      </div>
      <a href="dashboard.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
  </div>

  <?php if (!empty($msg)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($msg) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
  <?php endif; ?>

  <div class="card p-4">
      <form method="post" autocomplete="off">
          
          <!-- Points Configuration -->
          <div class="mb-4">
              <h5 class="section-title"><i class="bi bi-trophy-fill me-2" style="color: #FFD700;"></i>Points Configuration</h5>
              <p class="section-description">Set the point values for match outcomes</p>
              
              <div class="settings-grid">
                  <div class="setting-section">
                      <div class="setting-icon icon-win">
                          <i class="bi bi-trophy-fill"></i>
                      </div>
                      <label class="form-label">Points for Win</label>
                      <input type="number" name="points_win" class="form-control" value="<?= htmlspecialchars($settings['points_win'] ?? 3) ?>" required min="0">
                      <div class="form-text">Standard: 3 points per win</div>
                  </div>

                  <div class="setting-section">
                      <div class="setting-icon icon-draw">
                          <i class="bi bi-dash-circle-fill"></i>
                      </div>
                      <label class="form-label">Points for Draw</label>
                      <input type="number" name="points_draw" class="form-control" value="<?= htmlspecialchars($settings['points_draw'] ?? 1) ?>" required min="0">
                      <div class="form-text">Standard: 1 point per draw</div>
                  </div>

                  <div class="setting-section">
                      <div class="setting-icon icon-loss">
                          <i class="bi bi-x-circle-fill"></i>
                      </div>
                      <label class="form-label">Points for Loss</label>
                      <input type="number" name="points_loss" class="form-control" value="<?= htmlspecialchars($settings['points_loss'] ?? 0) ?>" required min="0">
                      <div class="form-text">Standard: 0 points per loss</div>
                  </div>
              </div>
          </div>

          <!-- Tie-Breaker Rules -->
          <div class="mb-4">
              <h5 class="section-title"><i class="bi bi-diagram-3-fill me-2" style="color: #6f42c1;"></i>Tie-Breaker Rules</h5>
              <p class="section-description">Define how to rank teams with equal points</p>
              
              <div class="setting-section">
                  <div class="setting-icon icon-tie">
                      <i class="bi bi-diagram-3-fill"></i>
                  </div>
                  <label class="form-label">Primary Tie-Breaker</label>
                  <select name="tie_breaker" class="form-select">
                      <option value="goal_difference" <?= ($settings['tie_breaker'] ?? '') === 'goal_difference' ? 'selected' : '' ?>>Goal Difference (Goals For - Goals Against)</option>
                      <option value="head_to_head" <?= ($settings['tie_breaker'] ?? '') === 'head_to_head' ? 'selected' : '' ?>>Head-to-Head Record</option>
                      <option value="goals_scored" <?= ($settings['tie_breaker'] ?? '') === 'goals_scored' ? 'selected' : '' ?>>Total Goals Scored</option>
                  </select>
                  <div class="form-text">Applied when multiple teams have the same points</div>
              </div>
          </div>

          <!-- Tournament Information -->
          <div class="mb-4">
              <h5 class="section-title"><i class="bi bi-calendar-event-fill me-2" style="color: #1E90FF;"></i>Tournament Information</h5>
              <p class="section-description">General tournament details</p>
              
              <div class="setting-section">
                  <div class="setting-icon icon-calendar">
                      <i class="bi bi-calendar-event-fill"></i>
                  </div>
                  <label class="form-label">Tournament Year</label>
                  <input type="number" name="tournament_year" class="form-control" value="<?= htmlspecialchars($settings['tournament_year'] ?? date('Y')) ?>" min="2000" max="2100">
                  <div class="form-text">The year this tournament takes place</div>
              </div>
          </div>

          <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn-submit">
                  <i class="bi bi-check2-circle"></i>
                  Save Settings
              </button>
          </div>
      </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>