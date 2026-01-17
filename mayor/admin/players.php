<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__ . '/../inc/auth.php';
require_admin();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

$team_id = $_GET['team_id'] ?? null;

// --- Handle form submissions ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $pdo->prepare('INSERT INTO players (team_id, fullname, number, position, is_captain, dob) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $_POST['team_id'],
            trim($_POST['fullname']),
            trim($_POST['number']),
            trim($_POST['position']),
            !empty($_POST['is_captain']) ? 1 : 0,
            $_POST['dob'] ?: null
        ]);
        header('Location: players.php?team_id=' . (int)$_POST['team_id']);
        exit;
    }

    if ($action === 'update') {
        $stmt = $pdo->prepare('UPDATE players SET fullname=?, number=?, position=?, is_captain=?, dob=? WHERE id=?');
        $stmt->execute([
            trim($_POST['fullname']),
            trim($_POST['number']),
            trim($_POST['position']),
            !empty($_POST['is_captain']) ? 1 : 0,
            $_POST['dob'] ?: null,
            (int)$_POST['player_id']
        ]);
        header('Location: players.php?team_id=' . (int)$_POST['team_id']);
        exit;
    }

    if ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM players WHERE id = ?');
        $stmt->execute([(int)$_POST['player_id']]);
        header('Location: players.php?team_id=' . (int)$_POST['team_id']);
        exit;
    }
}

// --- Fetch teams ---
$teams = $pdo->query('SELECT * FROM teams ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);

// --- Fetch players ---
$players = [];
$selected_team = null;
if ($team_id) {
    $stmt = $pdo->prepare('SELECT * FROM teams WHERE id = ?');
    $stmt->execute([$team_id]);
    $selected_team = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $stmt = $pdo->prepare('SELECT * FROM players WHERE team_id = ? ORDER BY fullname');
    $stmt->execute([$team_id]);
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Players Management — Mayoral Cup Admin</title>
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

.team-selector-card {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(255, 215, 0, 0.3);
    margin-bottom: 2rem;
}

.team-selector-label {
    background: rgba(255, 255, 255, 0.3);
    border: none;
    border-right: 2px solid rgba(255, 255, 255, 0.5);
    color: #000;
    font-weight: 600;
}

.form-select-custom {
    border: 2px solid rgba(255, 255, 255, 0.5);
    border-radius: 0 12px 12px 0;
    padding: 0.75rem 1rem;
    font-weight: 600;
    background: white;
    transition: all 0.3s ease;
}

.form-select-custom:focus {
    border-color: white;
    box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.3);
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
    transform: scale(1.01);
}

.player-name {
    font-weight: 600;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.badge-captain {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
    padding: 0.3rem 0.7rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

.player-number {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    color: white;
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.9rem;
    min-width: 45px;
    text-align: center;
    display: inline-block;
}

.btn-action {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-edit {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
}

.btn-delete {
    background: linear-gradient(135deg, #FF4500 0%, #DC143C 100%);
    color: white;
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

.form-check-input:checked {
    background-color: #FFD700;
    border-color: #FFD700;
}

.form-check-input:focus {
    border-color: #FFD700;
    box-shadow: 0 0 0 0.2rem rgba(255, 215, 0, 0.15);
}

.form-check-label {
    font-weight: 600;
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

.stats-badge {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
}

.modal-content {
    border-radius: 16px;
    border: none;
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

.modal-footer {
    border-top: none;
    padding: 1.5rem;
}

.text-danger-custom {
    color: #FF4500 !important;
}

.no-team-selected {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 16px;
    padding: 4rem 2rem;
    text-align: center;
}

.no-team-selected i {
    font-size: 4rem;
    color: #FFD700;
    margin-bottom: 1rem;
}

.no-team-selected h4 {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.no-team-selected p {
    color: #6c757d;
    margin: 0;
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
          <h3><i class="bi bi-person-lines-fill me-2" style="color: #1E90FF;"></i>Manage Players</h3>
          <p>Add and manage player rosters for each team</p>
      </div>
      <a href="teams.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Teams
      </a>
  </div>

  <!-- Team Selector -->
  <div class="card team-selector-card p-0">
      <form method="get" class="m-0">
          <div class="input-group">
              <span class="input-group-text team-selector-label">
                  <i class="bi bi-people-fill me-2"></i> Select Team
              </span>
              <select name="team_id" class="form-select form-select-custom" onchange="this.form.submit()">
                  <option value="">Choose a team to manage...</option>
                  <?php foreach ($teams as $t): ?>
                      <option value="<?= $t['id'] ?>" <?= $team_id == $t['id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($t['name']) ?>
                      </option>
                  <?php endforeach; ?>
              </select>
          </div>
      </form>
  </div>

  <?php if ($team_id && $selected_team): ?>

  <!-- Players Table -->
  <div class="card p-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
          <div class="card-header-custom mb-0">
              <i class="bi bi-list-ul" style="color: #1E90FF;"></i>
              Team Roster
          </div>
          <span class="stats-badge">
              <i class="bi bi-people"></i> <?= count($players) ?> Player(s)
          </span>
      </div>

      <div class="table-responsive">
          <table class="table">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Player Name</th>
                      <th>Number</th>
                      <th>Position</th>
                      <th>Date of Birth</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach ($players as $p): ?>
                  <tr>
                      <td><strong>#<?= htmlspecialchars($p['id']) ?></strong></td>
                      <td>
                          <div class="player-name">
                              <?= htmlspecialchars($p['fullname']) ?>
                              <?php if ($p['is_captain']): ?>
                                  <span class="badge-captain">
                                      <i class="bi bi-star-fill"></i> Captain
                                  </span>
                              <?php endif; ?>
                          </div>
                      </td>
                      <td>
                          <?php if ($p['number']): ?>
                              <span class="player-number"><?= htmlspecialchars($p['number']) ?></span>
                          <?php else: ?>
                              <span class="text-muted">—</span>
                          <?php endif; ?>
                      </td>
                      <td><?= htmlspecialchars($p['position'] ?: '—') ?></td>
                      <td>
                          <?php if ($p['dob']): ?>
                              <?= htmlspecialchars(date('d M Y', strtotime($p['dob']))) ?>
                          <?php else: ?>
                              <span class="text-muted">—</span>
                          <?php endif; ?>
                      </td>
                      <td>
                          <button class="btn btn-action btn-edit" 
                                  onclick='openEditModal(<?= json_encode($p, JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>)' 
                                  title="Edit Player">
                              <i class="bi bi-pencil-square"></i>
                          </button>
                          <button class="btn btn-action btn-delete" 
                                  onclick="confirmDelete(<?= (int)$p['id'] ?>, <?= (int)$team_id ?>)" 
                                  title="Delete Player">
                              <i class="bi bi-trash"></i>
                          </button>
                      </td>
                  </tr>
              <?php endforeach; ?>
              <?php if (empty($players)): ?>
                  <tr>
                      <td colspan="6">
                          <div class="empty-state">
                              <i class="bi bi-person-x"></i>
                              <p>No players registered yet. Add your first player below!</p>
                          </div>
                      </td>
                  </tr>
              <?php endif; ?>
              </tbody>
          </table>
      </div>
  </div>

  <!-- Add Player Form -->
  <div class="card p-4">
      <div class="card-header-custom">
          <i class="bi bi-person-plus-fill" style="color: #32CD32;"></i>
          Add New Player
      </div>
      <form method="post" autocomplete="off">
          <input type="hidden" name="action" value="add">
          <input type="hidden" name="team_id" value="<?= htmlspecialchars($team_id) ?>">
          
          <div class="row g-3">
              <div class="col-md-6">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="fullname" class="form-control" placeholder="e.g. John Smith" required>
              </div>
              <div class="col-md-3">
                  <label class="form-label">Jersey Number</label>
                  <input type="text" name="number" class="form-control" placeholder="e.g. 10">
              </div>
              <div class="col-md-3">
                  <label class="form-label">Position</label>
                  <input type="text" name="position" class="form-control" placeholder="e.g. Forward">
              </div>
              <div class="col-md-6">
                  <label class="form-label">Date of Birth</label>
                  <input type="date" name="dob" class="form-control">
              </div>
              <div class="col-md-6 d-flex align-items-end">
                  <div class="form-check">
                      <input type="checkbox" name="is_captain" class="form-check-input" id="captain">
                      <label class="form-check-label" for="captain">
                          <i class="bi bi-star-fill me-1" style="color: #FFD700;"></i>Team Captain
                      </label>
                  </div>
              </div>
          </div>
          
          <div class="text-end mt-4">
              <button class="btn-submit">
                  <i class="bi bi-check2-circle"></i>
                  Add Player
              </button>
          </div>
      </form>
  </div>

  <?php else: ?>
      <div class="card">
          <div class="no-team-selected">
              <i class="bi bi-people"></i>
              <h4>No Team Selected</h4>
              <p>Please select a team from the dropdown above to view and manage players.</p>
          </div>
      </div>
  <?php endif; ?>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="editForm">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="player_id" id="edit_player_id">
        <input type="hidden" name="team_id" value="<?= htmlspecialchars($team_id ?? '') ?>">
        
        <div class="modal-header">
          <h5 class="modal-title">
              <i class="bi bi-pencil-square me-2"></i>Edit Player
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input name="fullname" id="edit_fullname" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jersey Number</label>
            <input name="number" id="edit_number" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Position</label>
            <input name="position" id="edit_position" class="form-control">
          </div>
          <div class="mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" id="edit_dob" class="form-control">
          </div>
          <div class="form-check">
            <input type="checkbox" name="is_captain" id="edit_is_captain" class="form-check-input">
            <label class="form-check-label" for="edit_is_captain">
                <i class="bi bi-star-fill me-1" style="color: #FFD700;"></i>Team Captain
            </label>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn-submit">
              <i class="bi bi-check2-circle"></i>
              Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<form id="deleteForm" method="post" style="display:none;">
  <input type="hidden" name="action" value="delete">
  <input type="hidden" name="player_id" id="delete_id">
  <input type="hidden" name="team_id" value="<?= htmlspecialchars($team_id ?? '') ?>">
</form>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let editModal = new bootstrap.Modal(document.getElementById('editModal'));

function openEditModal(p) {
  document.getElementById('edit_player_id').value = p.id;
  document.getElementById('edit_fullname').value = p.fullname || '';
  document.getElementById('edit_number').value = p.number || '';
  document.getElementById('edit_position').value = p.position || '';
  document.getElementById('edit_dob').value = p.dob || '';
  document.getElementById('edit_is_captain').checked = p.is_captain == 1;
  editModal.show();
}

function confirmDelete(id, team) {
  if (confirm('Are you sure you want to delete this player? This action cannot be undone.')) {
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}
</script>
</body>
</html>