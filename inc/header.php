<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin KP CAMP</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- AdminLTE -->
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <!-- Bootstrap (AdminLTE v3 pakai Bootstrap 4, bukan 5) -->
  <link rel="stylesheet" href="plugins/bootstrap/css/bootstrap.min.css">
  <!-- Optional Theme -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left: Toggle button -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
          <i class="fas fa-bars"></i>
        </a>
      </li>
    </ul>

    <!-- Right: User info & logout -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item d-none d-sm-inline-block">
        <span class="nav-link">
          Halo, <?= $_SESSION['admin']['nama'] ?? 'Admin' ?>
        </span>
      </li>
      <li class="nav-item">
        <a class="nav-link text-danger" href="logout.php" title="Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="dashboard.php" class="brand-link">
      <i class="fas fa-campground mx-2"></i>
      <span class="brand-text font-weight-light">KP CAMP</span>
    </a>

    <!-- Sidebar Menu -->
    <div class="sidebar">
      <nav class="mt-3">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <li class="nav-item">
            <a href="index.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Dashboard</p>
            </a>
          </li>
  
          <li class="nav-item">
            <a href="data-penjualan.php" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>Penjualan</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="input-nota.php" class="nav-link">
              <i class="nav-icon fas fa-file-invoice"></i>
              <p>Input Nota</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="input-item.php" class="nav-link">
              <i class="nav-icon fas fa-box-open"></i>
              <p>Input Item</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="kategori.php" class="nav-link">
              <i class="nav-icon fas fa-box-open"></i>
              <p>Kategori</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="progress.php" class="nav-link">
              <i class="nav-icon fas fa-box-open"></i>
              <p>Progress</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="report.php" class="nav-link">
              <i class="nav-icon fas fa-chart-line"></i>
              <p>Report</p>
            </a>
          </li>

          <li class="nav-item">
            <a href="user.php" class="nav-link">
              <i class="nav-icon fas fa-users-cog"></i>
              <p>User</p>
            </a>
          </li>
        </ul>
      </nav>
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (optional) -->
    <div class="content-header">
      <div class="container-fluid">
        <!-- Bisa diisi breadcrumb atau judul halaman -->
      </div>
    </div>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
