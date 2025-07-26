<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'inc/db.php';
include 'inc/header.php';

// Ambil semua transaksi
$transaksi = $conn->query("SELECT * FROM transaksi ORDER BY created_at DESC");
?>

<div class="container py-4">
  <h2 class="mb-4">Riwayat Nota Transaksi</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Nama</th>
          <th>Telepon</th>
          <th>Periode</th>
          <th>Total</th>
          <th>DP</th>
          <th>Sisa</th>
          <th>Waktu</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while ($row = $transaksi->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['phone']) ?></td>
          <td><?= $row['tanggal_from'] ?> s/d <?= $row['tanggal_to'] ?></td>
          <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
          <td>Rp <?= number_format($row['dp'], 0, ',', '.') ?></td>
          <td>Rp <?= number_format($row['sisa'], 0, ',', '.') ?></td>
          <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
          <td>
            <a href="nota-view.php?id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-primary">Lihat Nota</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'inc/footer.php'; ?>
