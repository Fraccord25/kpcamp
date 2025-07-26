<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';
include 'inc/header.php';

$phone = $_GET['phone'] ?? '';
if (!$phone) {
  echo "<div class='alert alert-warning'>Nomor telepon tidak ditemukan.</div>";
  exit();
}

$user = $conn->query("SELECT * FROM user WHERE phone = '$phone'")->fetch_assoc();
$transaksi = $conn->query("SELECT * FROM transaksi WHERE phone = '$phone' ORDER BY id DESC");
?>

<div class="container py-4">
  <h2 class="mb-4">Riwayat Pemesanan: <?= htmlspecialchars($user['nama']) ?></h2>
  <p><strong>Telepon:</strong> <?= htmlspecialchars($user['phone']) ?> <br>
     <strong>Alamat:</strong> <?= htmlspecialchars($user['alamat']) ?></p>

  <?php while ($row = $transaksi->fetch_assoc()): ?>
    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <strong>Transaksi #<?= $row['id'] ?></strong> - <?= date('d M Y', strtotime($row['created_at'])) ?>
      </div>
      <div class="card-body">
        <p>
          <strong>Tanggal Sewa:</strong> <?= date('d/m/Y', strtotime($row['tanggal_from'])) ?> -
          <?= date('d/m/Y', strtotime($row['tanggal_to'])) ?><br>
          <strong>Total:</strong> Rp <?= number_format($row['total']) ?> <br>
          <strong>DP:</strong> Rp <?= number_format($row['dp']) ?> <br>
          <strong>Sisa:</strong> Rp <?= number_format($row['sisa']) ?> <br>
          <strong>Status:</strong>
<?php if ($row['status_pembayaran'] === 'Lunas'): ?>
  <span class="badge bg-success">Lunas</span>
<?php elseif ($row['status_pembayaran'] === 'DP'): ?>
  <span class="badge bg-warning text-dark">DP</span>
<?php else: ?>
  <span class="badge bg-secondary"><?= htmlspecialchars($row['status_pembayaran']) ?></span>
<?php endif; ?>
        </p>

        <h6>Detail Item:</h6>
        <table class="table table-bordered table-sm">
          <thead>
            <tr>
              <th>Nama Item</th>
              <th>Hari</th>
              <th>Harga</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $id_trans = $row['id'];
              $detail = $conn->query("
                SELECT td.*, i.nama 
                FROM transaksi_detail td 
                JOIN item i ON i.id = td.item_id 
                WHERE td.transaksi_id = $id_trans
              ");
              while ($d = $detail->fetch_assoc()):
            ?>
              <tr>
                <td><?= htmlspecialchars($d['nama']) ?></td>
                <td><?= $d['jumlah_hari'] ?></td>
                <td>Rp <?= number_format($d['harga']) ?></td>
                <td>Rp <?= number_format($d['subtotal']) ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<?php include 'inc/footer.php'; ?>
