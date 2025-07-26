<?php
include 'inc/db.php';
include 'inc/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $nama = trim($_POST['nama']);

  if (!empty($nama)) {
    $stmt = $conn->prepare("INSERT INTO kategori (nama) VALUES (?)");
    $stmt->bind_param("s", $nama);
    $stmt->execute();
    echo "<div class='alert alert-success'>Kategori berhasil ditambahkan!</div>";
  } else {
    echo "<div class='alert alert-danger'>Nama kategori tidak boleh kosong.</div>";
  }
}
?>

<div class="container mt-4">
  <h3>Tambah Kategori</h3>
  <form method="POST" class="mt-3">
    <div class="mb-3">
      <label for="nama" class="form-label">Nama Kategori</label>
      <input type="text" class="form-control" id="nama" name="nama" required>
    </div>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <a href="kategori.php" class="btn btn-secondary">Kembali</a>
  </form>
</div>

<?php include 'inc/footer.php'; ?>
