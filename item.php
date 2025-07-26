<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'inc/db.php';
include 'inc/header.php';

// Ambil semua kategori untuk dropdown
$kategoriList = $conn->query("SELECT * FROM kategori ORDER BY nama");

// Tambah item baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
  $kategori_id = $_POST['kategori_id'];
  $nama = $_POST['nama'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];

  // Upload gambar
  $foto = '';
  if ($_FILES['foto']['name']) {
    $uploadDir = 'uploads/';
    $foto = time() . '-' . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);
  }

  $stmt = $conn->prepare("INSERT INTO item (kategori_id, nama, harga, deskripsi, foto) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("isiss", $kategori_id, $nama, $harga, $deskripsi, $foto);
  $stmt->execute();
  $stmt->close();
  echo "<div class='alert alert-success'>Item berhasil ditambahkan!</div>";
}

// Hapus item
if (isset($_GET['hapus'])) {
  $id = (int)$_GET['hapus'];
  $conn->query("DELETE FROM item WHERE id = $id");
  echo "<div class='alert alert-danger'>Item berhasil dihapus.</div>";
}

// Ambil semua item
$itemList = $conn->query("SELECT item.*, kategori.nama as kategori FROM item 
                          JOIN kategori ON item.kategori_id = kategori.id 
                          ORDER BY item.id DESC");
?>

<div class="container py-4">
  <h2 class="mb-4">Manajemen Item</h2>

  <form method="POST" enctype="multipart/form-data" class="row g-3 mb-4">
    <div class="col-md-3">
      <select name="kategori_id" class="form-select" required>
        <option value="">-- Pilih Kategori --</option>
        <?php while ($k = $kategoriList->fetch_assoc()): ?>
          <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <input type="text" name="nama" class="form-control" placeholder="Nama Item" required>
    </div>
    <div class="col-md-2">
      <input type="number" name="harga" class="form-control" placeholder="Harga per hari" required>
    </div>
    <div class="col-md-4">
      <input type="text" name="deskripsi" class="form-control" placeholder="Deskripsi" required>
    </div>
    <div class="col-md-4">
      <input type="file" name="foto" class="form-control">
    </div>
    <div class="col-md-2">
      <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah</button>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Foto</th>
          <th>Item</th>
          <th>Kategori</th>
          <th>Harga</th>
          <th>Deskripsi</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while ($item = $itemList->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <?php if ($item['foto']): ?>
              <img src="uploads/<?= htmlspecialchars($item['foto']) ?>" alt="" width="50">
            <?php else: ?>
              <em>Tidak ada</em>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($item['nama']) ?></td>
          <td><?= htmlspecialchars($item['kategori']) ?></td>
          <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
          <td><?= htmlspecialchars($item['deskripsi']) ?></td>
          <td>
            <a href="?hapus=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus item ini?')">Hapus</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'inc/footer.php'; ?>
