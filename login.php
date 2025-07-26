<?php
session_start();
include 'inc/db.php';

if (isset($_SESSION['admin'])) {
  header('Location: index.php');
  exit;
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = $_POST['email'] ?? '';
  $password = md5($_POST['password'] ?? '');

  $query = $conn->prepare("SELECT * FROM admin WHERE email = ? AND password = ?");
  $query->bind_param("ss", $email, $password);
  $query->execute();
  $result = $query->get_result();

  if ($result->num_rows == 1) {
    $_SESSION['admin'] = $result->fetch_assoc();
    $success = "Login berhasil! Mengalihkan ke dashboard...";
    echo '<meta http-equiv="refresh" content="1;url=index.php">';
  } else {
    $error = "Email atau Password salah.";
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login Admin - KP CAMP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    .login-box {
      margin-left: auto;
      margin-right: auto;
      max-width: 420px;
      width: 100%;
      background: white;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 0 20px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
  <div class="container-fluid px-3 d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="login-box text-center w-100">
      <div class="mb-4">
        <i class="fas fa-campground logo" style="font-size: 3rem;"></i><br>
        <span class="brand-text">KP CAMP Admin</span>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php elseif ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>

      <form method="POST" class="text-start">
        <div class="mb-3">
          <label for="email" class="form-label">Email Admin</label>
          <input type="email" name="email" class="form-control" required placeholder="admin@kpcamp.local">
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required placeholder="Masukkan Password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>
  </div>
</body>
</html>
