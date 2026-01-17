<?php
require_once __DIR__ . '/../inc/db.php';
require_once __DIR__.'/../inc/auth.php'; 
require_admin();

// Build absolute URL for logo
$logo_url = sprintf(
    "%s://%s/mayor/assets/images/matatiele-municipal-logo.jpg",
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http',
    $_SERVER['HTTP_HOST']
);

// Handle Add Team
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $stmt = $pdo->prepare('INSERT INTO teams (sport_id, name, short_name, coach, home_ground) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$_POST['sport_id'], $_POST['name'], $_POST['short_name'], $_POST['coach'], $_POST['home_ground']]);
        header('Location: teams.php'); exit;
    } elseif ($action === 'edit') {
        $stmt = $pdo->prepare('UPDATE teams SET sport_id=?, name=?, short_name=?, coach=?, home_ground=? WHERE id=?');
        $stmt->execute([$_POST['sport_id'], $_POST['name'], $_POST['short_name'], $_POST['coach'], $_POST['home_ground'], $_POST['team_id']]);
        header('Location: teams.php'); exit;
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare('DELETE FROM teams WHERE id=?');
        $stmt->execute([$_POST['team_id']]);
        header('Location: teams.php'); exit;
    }
}

// Fetch Data
$sports = $pdo->query('SELECT * FROM sports ORDER BY name')->fetchAll();
$teams = $pdo->query('
    SELECT t.*, s.name AS sport 
    FROM teams t 
    JOIN sports s ON s.id = t.sport_id 
    ORDER BY s.name, t.name
')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Teams Management — Mayoral Cup Admin</title>
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

.team-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.25rem;
}

.team-short {
    color: #6c757d;
    font-size: 0.85rem;
}

.badge-sport {
    background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
    color: #000;
    padding: 0.4rem 0.8rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.8rem;
}

.btn-action {
    padding: 0.4rem 0.8rem;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    border: none;
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

.btn-players {
    background: linear-gradient(135deg, #1E90FF 0%, #0066CC 100%);
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
          <h3><i class="bi bi-people-fill me-2" style="color: #FFD700;"></i>Manage Teams</h3>
          <p>View, add, edit, and delete teams in the tournament</p>
      </div>
      <a href="dashboard.php" class="btn-back">
          <i class="bi bi-arrow-left"></i> Back to Dashboard
      </a>
  </div>

  <!-- Teams Table -->
  <div class="card p-4">
      <div class="card-header-custom">
          <i class="bi bi-list-ul" style="color: #1E90FF;"></i>
          Registered Teams
      </div>
      <div class="table-responsive">
          <table class="table">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Sport</th>
                      <th>Team</th>
                      <th>Coach</th>
                      <th>Home Ground</th>
                      <th>Actions</th>
                  </tr>
              </thead>
              <tbody>
              <?php foreach ($teams as $t): ?>
                  <tr>
                      <td><strong>#<?= htmlspecialchars($t['id']) ?></strong></td>
                      <td><span class="badge-sport"><?= htmlspecialchars($t['sport']) ?></span></td>
                      <td>
                          <div class="team-name"><?= htmlspecialchars($t['name']) ?></div>
                          <div class="team-short"><?= htmlspecialchars($t['short_name'] ?? '-') ?></div>
                      </td>
                      <td><?= htmlspecialchars($t['coach'] ?? '-') ?></td>
                      <td><?= htmlspecialchars($t['home_ground'] ?? '-') ?></td>
                      <td>
                          <button class="btn btn-action btn-edit" data-bs-toggle="modal" data-bs-target="#editTeamModal<?= $t['id'] ?>" title="Edit Team">
                              <i class="bi bi-pencil-square"></i>
                          </button>
                          <button class="btn btn-action btn-delete" data-bs-toggle="modal" data-bs-target="#deleteTeamModal<?= $t['id'] ?>" title="Delete Team">
                              <i class="bi bi-trash"></i>
                          </button>
                          <a href="players.php?team_id=<?= $t['id'] ?>" class="btn btn-action btn-players" title="View Players">
                              <i class="bi bi-person-lines-fill"></i>
                          </a>
                      </td>
                  </tr>

                  <!-- Edit Modal -->
                  <div class="modal fade" id="editTeamModal<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="post">
                          <input type="hidden" name="action" value="edit">
                          <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                          <div class="modal-header">
                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Team</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label">Sport</label>
                              <select name="sport_id" class="form-select" required>
                                  <?php foreach ($sports as $s): ?>
                                  <option value="<?= htmlspecialchars($s['id']) ?>" <?= $s['id']==$t['sport_id']?'selected':'' ?>>
                                      <?= htmlspecialchars($s['name']) ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Team Name</label>
                                <input name="name" class="form-control" value="<?= htmlspecialchars($t['name']) ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Short Name</label>
                                <input name="short_name" class="form-control" value="<?= htmlspecialchars($t['short_name']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Coach</label>
                                <input name="coach" class="form-control" value="<?= htmlspecialchars($t['coach']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Home Ground</label>
                                <input name="home_ground" class="form-control" value="<?= htmlspecialchars($t['home_ground']) ?>">
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-submit">Save Changes</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  <!-- Delete Modal -->
                  <div class="modal fade" id="deleteTeamModal<?= $t['id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="post">
                          <input type="hidden" name="action" value="delete">
                          <input type="hidden" name="team_id" value="<?= $t['id'] ?>">
                          <div class="modal-header">
                            <h5 class="modal-title text-danger-custom"><i class="bi bi-exclamation-triangle me-2"></i>Delete Team</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p>Are you sure you want to delete <strong><?= htmlspecialchars($t['name']) ?></strong>?</p>
                            <p class="text-muted mb-0"><small>This will also delete all associated players and data. This action cannot be undone.</small></p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-action btn-delete">Delete Team</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

              <?php endforeach; ?>
              <?php if (empty($teams)): ?>
                  <tr>
                      <td colspan="6">
                          <div class="empty-state">
                              <i class="bi bi-people"></i>
                              <p>No teams registered yet. Add your first team below!</p>
                          </div>
                      </td>
                  </tr>
              <?php endif; ?>
              </tbody>
          </table>
      </div>
  </div>

  <!-- Add Team Form -->
  <div class="card p-4">
      <div class="card-header-custom">
          <i class="bi bi-plus-circle" style="color: #32CD32;"></i>
          Add New Team
      </div>
      <form method="post" autocomplete="off">
          <input type="hidden" name="action" value="add">
          <div class="row g-3">
              <div class="col-md-6">
                  <label class="form-label">Sport</label>
                  <select name="sport_id" class="form-select" required>
                      <option value="">Select Sport...</option>
                      <?php foreach ($sports as $s): ?>
                          <option value="<?= htmlspecialchars($s['id']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="col-md-6">
                  <label class="form-label">Team Name</label>
                  <input type="text" name="name" class="form-control" placeholder="e.g. Matatiele United" required>
              </div>
              <div class="col-md-6">
                  <label class="form-label">Short Name</label>
                  <input type="text" name="short_name" class="form-control" placeholder="e.g. MAT">
              </div>
              <div class="col-md-6">
                  <label class="form-label">Coach</label>
                  <input type="text" name="coach" class="form-control" placeholder="e.g. John Doe">
              </div>
              <div class="col-12">
                  <label class="form-label">Home Ground</label>
                  <input type="text" name="home_ground" class="form-control" placeholder="e.g. Matatiele Stadium">
              </div>
          </div>
          <div class="text-end mt-4">
              <button class="btn-submit">
                  <i class="bi bi-check2-circle"></i>
                  Add Team
              </button>
          </div>
      </form>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>