<?php
session_start();
require '../includes/config.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch();

  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin'] = $admin['username'];
    header("Location: dashboard.php");
    exit;
  } else {
    $error = "Invalid credentials.";
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <style>
    body{font-family:Arial;background:#f6f4ee;display:flex;justify-content:center;align-items:center;height:100vh;}
    form{background:#fff;padding:30px;border-radius:12px;box-shadow:0 0 12px rgba(0,0,0,0.1);width:300px;}
    input{width:100%;padding:10px;margin:8px 0;border:1px solid #ccc;border-radius:8px;}
    button{background:#2E6F3A;color:#fff;border:none;padding:10px;width:100%;border-radius:8px;}
  </style>
</head>
<body>
  <form method="post">
    <h3>Admin Login</h3>
    <?php if($error): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
  </form>
</body>
</html>
