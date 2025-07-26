<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';
include 'inc/header.php';

// Tambah user baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $phone = $_POST['phone'];
  $alamat = $_POST['alamat'];
  $email = $_POST['email'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $conn->query("INSERT INTO user (nama, phone, alamat, email, password) VALUES ('$nama', '$phone', '$alamat', '$email', '$password')");
  echo "<div class='alert alert-success'>User berhasil ditambahkan!</div>";
}

// Ambil data semua user
$users = $conn->query("SELECT * FROM user ORDER BY id DESC");
?>

<!-- Content Wrapper -->
<section class="content">
  <div class="container-fluid py-3">
    <div class="card card-primary">
      <div class="card-header">
      <h1 class="h3">Data Pengguna</h1>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">

      
      <!-- Tabel User -->
      <div class="card mt-4 shadow-sm">
        <div class="card-header bg-info text-white">
          <h3 class="card-title">Daftar User</h3>
        </div>
        <div class="card-body table-responsive p-0">
          <table class="table table-bordered table-hover text-nowrap">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width: 60px;">No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Alamat</th>
                <th>Email</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1; while ($row = $users->fetch_assoc()): ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td>
                  <a href="user-history.php?phone=<?= urlencode($row['phone']) ?>">
                    <?= htmlspecialchars($row['nama']) ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td><?= htmlspecialchars($row['alamat']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
              </tr>
              <?php endwhile; ?>
              <?php if ($users->num_rows === 0): ?>
              <tr>
                <td colspan="5" class="text-center text-muted">Belum ada data pengguna.</td>
              </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'inc/footer.php'; ?>
