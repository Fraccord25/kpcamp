<?php
include 'inc/db.php';

// Proses Edit Kategori
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
  $id = (int)$_POST['edit_id'];
  $nama = trim($_POST['edit_nama']);
  if ($nama !== '') {
    $stmt = $conn->prepare("UPDATE kategori SET nama = ? WHERE id = ?");
    $stmt->bind_param("si", $nama, $id);
    $stmt->execute();
    echo "<script>alert('Kategori berhasil diperbarui.');window.location='kategori.php';</script>";
    exit;
  }
}

// Proses Hapus Kategori
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];

  // Hapus item terkait dulu
  $conn->query("DELETE FROM item WHERE kategori_id = $id");

  // Hapus kategori
  $conn->query("DELETE FROM kategori WHERE id = $id");

  echo "<script>alert('Kategori dan semua item di dalamnya berhasil dihapus.');window.location='kategori.php';</script>";
  exit;
}

include 'inc/header.php';

$kategori = $conn->query("SELECT * FROM kategori ORDER BY id DESC");
?>

<section class="content">
  <div class="container-fluid py-3">
    <div class="card card-primary">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Manajemen Kategori</h3>
        <a href="input-kategori.php" class="btn btn-success btn-sm">
          <i class="fas fa-plus"></i> Tambah Kategori
        </a>
      </div>

      <div class="card-body table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle text-nowrap">
          <thead class="table-light">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Nama Kategori</th>
              <th style="width: 130px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($row = $kategori->fetch_assoc()): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['nama']) ?></td>
              <td>
                <!-- Tombol Edit -->
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">
                  <i class="fas fa-edit"></i>
                </button>

                <!-- Tombol Hapus -->
                <a href="?delete=<?= $row['id'] ?>"
                   onclick="return confirm('Kategori ini akan dihapus beserta semua item di dalamnya. Lanjutkan?')"
                   class="btn btn-danger btn-sm">
                  <i class="fas fa-trash-alt"></i>
                </a>
              </td>
            </tr>

            <!-- Modal Edit -->
            <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel<?= $row['id'] ?>" aria-hidden="true">
              <div class="modal-dialog">
                <form method="POST" class="modal-content">
                  <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editModalLabel<?= $row['id'] ?>">Edit Kategori</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="edit_id" value="<?= $row['id'] ?>">
                    <div class="mb-3">
                      <label>Nama Kategori</label>
                      <input type="text" name="edit_nama" class="form-control" required value="<?= htmlspecialchars($row['nama']) ?>">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                  </div>
                </form>
              </div>
            </div>

            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php include 'inc/footer.php'; ?>
