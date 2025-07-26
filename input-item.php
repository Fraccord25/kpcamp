<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'inc/db.php';

// --- Tambah item baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nama_item'])) {
  $kategori = $_POST['kategori_id'];
  $nama = $_POST['nama_item'];
  $harga = $_POST['harga'];
  $deskripsi = $_POST['deskripsi'];
  $foto = "";

  if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $folder = 'uploads/';
    $nama_file = uniqid() . '-' . basename($_FILES['foto']['name']);
    move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $nama_file);
    $foto = $folder . $nama_file;
  }

  $stmt = $conn->prepare("INSERT INTO item (kategori_id, nama, harga, deskripsi, foto) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("isiss", $kategori, $nama, $harga, $deskripsi, $foto);
  $stmt->execute();
  header("Location: input-item.php");
  exit();
}

// --- Edit item
if (isset($_POST['update_id'])) {
  $id = $_POST['update_id'];
  $kategori = $_POST['edit_kategori'];
  $nama = $_POST['edit_nama_item'];
  $harga = $_POST['edit_harga'];
  $deskripsi = $_POST['edit_deskripsi'];
  $foto = $_POST['old_foto'];

  if (isset($_FILES['edit_foto']) && $_FILES['edit_foto']['error'] === 0) {
    $folder = 'uploads/';
    $nama_file = uniqid() . '-' . basename($_FILES['edit_foto']['name']);
    move_uploaded_file($_FILES['edit_foto']['tmp_name'], $folder . $nama_file);
    $foto = $folder . $nama_file;
  }

  $stmt = $conn->prepare("UPDATE item SET kategori_id=?, nama=?, harga=?, deskripsi=?, foto=? WHERE id=?");
  $stmt->bind_param("isissi", $kategori, $nama, $harga, $deskripsi, $foto, $id);
  $stmt->execute();
  header("Location: input-item.php");
  exit();
}

// --- Hapus item
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $check = $conn->query("SELECT COUNT(*) as total FROM transaksi_detail WHERE item_id = $id");
  $row = $check->fetch_assoc();

  if ($row['total'] > 0) {
    echo "<script>alert('Item tidak bisa dihapus karena masih digunakan dalam transaksi!'); window.location='input-item.php';</script>";
    exit();
  }

  $conn->query("DELETE FROM item WHERE id = $id");
  header("Location: input-item.php");
  exit();
}

// --- Ambil data
$kategori = $conn->query("SELECT * FROM kategori");
$items = $conn->query("SELECT item.*, kategori.nama AS nama_kategori FROM item JOIN kategori ON item.kategori_id = kategori.id");

include 'inc/header.php';
?>

<!-- Tombol Tambah -->
<div class="mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahItem">+ Tambah Item</button>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahItem" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form method="post" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Form Tambah Item</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6 col-lg-4">
            <label>Kategori</label>
            <select name="kategori_id" class="form-control" required>
              <option value="">Pilih</option>
              <?php foreach ($kategori as $k): ?>
                <option value="<?= $k['id'] ?>"><?= htmlspecialchars($k['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6 col-lg-4">
            <label>Nama Item</label>
            <input type="text" name="nama_item" class="form-control" required>
          </div>
          <div class="col-md-6 col-lg-4">
            <label>Harga</label>
            <input type="number" name="harga" class="form-control" required>
          </div>
          <div class="col-md-12 col-lg-6">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="2"></textarea>
          </div>
          <div class="col-md-12 col-lg-6">
            <label>Foto</label>
            <input type="file" name="foto" class="form-control" accept="image/*">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Tabel -->
<div class="card">
  <div class="card-header bg-secondary text-white">
    <h3 class="card-title">Daftar Item</h3>
  </div>
  <div class="card-body table-responsive">
    <table class="table table-bordered table-striped align-middle text-nowrap">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Kategori</th>
          <th>Nama</th>
          <th>Harga</th>
          <th>Deskripsi</th>
          <th>Foto</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; foreach ($items as $item): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($item['nama_kategori']) ?></td>
          <td><?= htmlspecialchars($item['nama']) ?></td>
          <td>Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
          <td><?= htmlspecialchars($item['deskripsi']) ?></td>
          <td>
            <?php if ($item['foto']): ?>
              <img src="<?= $item['foto'] ?>" width="60" class="img-thumbnail">
            <?php endif; ?>
          </td>
          <td>
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editModal<?= $item['id'] ?>">Edit</button>
            <a href="?delete=<?= $item['id'] ?>" onclick="return confirm('Hapus item ini?')" class="btn btn-sm btn-danger">Hapus</a>
          </td>
        </tr>

        <!-- Modal Edit -->
        <div class="modal fade" id="editModal<?= $item['id'] ?>" tabindex="-1">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <form method="post" enctype="multipart/form-data" class="modal-content">
              <input type="hidden" name="update_id" value="<?= $item['id'] ?>">
              <input type="hidden" name="old_foto" value="<?= $item['foto'] ?>">
              <div class="modal-header">
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="row g-3">
                  <div class="col-md-4">
                    <label>Kategori</label>
                    <select name="edit_kategori" class="form-control" required>
                      <?php foreach ($kategori as $kat): ?>
                        <option value="<?= $kat['id'] ?>" <?= $kat['id'] == $item['kategori_id'] ? 'selected' : '' ?>>
                          <?= htmlspecialchars($kat['nama']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label>Nama Item</label>
                    <input type="text" name="edit_nama_item" class="form-control" value="<?= htmlspecialchars($item['nama']) ?>" required>
                  </div>
                  <div class="col-md-4">
                    <label>Harga</label>
                    <input type="number" name="edit_harga" class="form-control" value="<?= $item['harga'] ?>" required>
                  </div>
                  <div class="col-md-12">
                    <label>Deskripsi</label>
                    <textarea name="edit_deskripsi" class="form-control" rows="2"><?= htmlspecialchars($item['deskripsi']) ?></textarea>
                  </div>
                  <div class="col-md-6">
                    <label>Ganti Foto</label>
                    <input type="file" name="edit_foto" class="form-control" accept="image/*">
                  </div>
                  <div class="col-md-6">
                    <?php if ($item['foto']): ?>
                      <img src="<?= $item['foto'] ?>" class="img-thumbnail mt-2" width="80">
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              </div>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div>
</section>
</div>

<?php include 'inc/footer.php'; ?>
