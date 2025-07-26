<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';
include 'inc/header.php';

// Ambil filter tanggal dari dan sampai
$tanggal_dari = $_GET['tanggal_dari'] ?? date('Y-m-d');
$tanggal_sampai = $_GET['tanggal_sampai'] ?? date('Y-m-d');

// Validasi dan konversi ke SQL WHERE
$filter = "WHERE DATE(created_at) BETWEEN '$tanggal_dari' AND '$tanggal_sampai'";

// Query transaksi
$pembayaran = $conn->query("SELECT nama, phone, total, dp, sisa, created_at FROM transaksi $filter ORDER BY created_at DESC");

// Total pendapatan
$total_hari_ini = $conn->query("SELECT SUM(total) as total_harian FROM transaksi $filter")->fetch_assoc();
?>

<!-- Content Header (Page header) -->
<div class="content-header">
  <div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h1 class="m-0">Laporan Transaksi Sewa</h1>
    </div>
  </div>
</div>

<!-- Main content -->
<section class="content">
  <div class="container-fluid">

    <!-- Filter Tanggal -->
    <form method="GET" class="row g-3 mb-3">
      <div class="col-md-3">
        <label for="tanggal_dari" class="form-label">Dari Tanggal</label>
        <input type="date" id="tanggal_dari" name="tanggal_dari" class="form-control" value="<?= $tanggal_dari ?>">
      </div>
      <div class="col-md-3">
        <label for="tanggal_sampai" class="form-label">Sampai Tanggal</label>
        <input type="date" id="tanggal_sampai" name="tanggal_sampai" class="form-control" value="<?= $tanggal_sampai ?>">
      </div>
      <div class="col-md-2 align-self-end">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
      </div>
    </form>

    <!-- Total Penjualan -->
    <div class="alert alert-info">
      <strong>Total Penjualan <?= date('d M Y', strtotime($tanggal_dari)) ?> s/d <?= date('d M Y', strtotime($tanggal_sampai)) ?>:</strong>
      Rp <?= number_format($total_hari_ini['total_harian'] ?? 0, 0, ',', '.') ?>
    </div>

    <!-- Tabel Transaksi -->
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h3 class="card-title">Data Transaksi</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-striped table-hover mb-0">
          <thead>
            <tr>
              <th>Nama</th>
              <th>Telepon</th>
              <th>Total</th>
              <th>DP</th>
              <th>Sisa</th>
              <th>Status</th>
              <th>Waktu</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($pembayaran->num_rows > 0): ?>
              <?php while ($row = $pembayaran->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['nama']) ?></td>
                  <td><?= htmlspecialchars($row['phone']) ?></td>
                  <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format($row['dp'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format($row['sisa'], 0, ',', '.') ?></td>
                  <td>
                    <?php if ($row['sisa'] <= 0): ?>
                      <span class="badge bg-success">Lunas</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">DP</span>
                    <?php endif; ?>
                  </td>
                  <td><?= $row['created_at'] ? date('d/m/Y H:i', strtotime($row['created_at'])) : '-' ?></td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="text-center">Tidak ada transaksi pada rentang tanggal ini.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</section>

<?php include 'inc/footer.php'; ?>
