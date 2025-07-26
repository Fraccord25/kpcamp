<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'inc/db.php';

// Auto redirect ke hari ini jika tidak ada filter
if (!isset($_GET['tanggal'])) {
  header("Location: progress.php?tanggal=" . date('Y-m-d'));
  exit();
}

$tanggal = $_GET['tanggal'];

// Jika ada input bayar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar_id'])) {
  $id = (int)$_POST['bayar_id'];
  $bayar = (int)$_POST['nominal'];

  $trans = $conn->query("SELECT dp, total FROM transaksi WHERE id = $id")->fetch_assoc();
  $dp_baru = $trans['dp'] + $bayar;
  $sisa = max(0, $trans['total'] - $dp_baru);
  $status = ($sisa <= 0) ? 'Lunas' : 'DP';

  $stmt = $conn->prepare("UPDATE transaksi SET dp=?, sisa=?, status_pembayaran=? WHERE id=?");
  $stmt->bind_param("iisi", $dp_baru, $sisa, $status, $id);
  $stmt->execute();
  header("Location: progress.php?tanggal=$tanggal");
  exit();
}

include 'inc/header.php';

// Ambil data transaksi
$sql = "
  SELECT * FROM transaksi
  WHERE DATE(created_at) = '$tanggal'
  ORDER BY created_at DESC
";
$data = $conn->query($sql);

$totalHarian = $conn->query("SELECT SUM(dp) as total FROM transaksi WHERE DATE(created_at) = '$tanggal'")->fetch_assoc();
?>

<div class="content-header">
  <div class="container-fluid">
    <h1 class="m-0 mb-3">Progress Pemesanan</h1>
  </div>
</div>

<section class="content">
  <div class="container-fluid">

    <!-- Filter Tanggal -->
    <form method="GET" class="row g-2 mb-3">
      <div class="col-md-3 col-sm-6">
        <label for="tanggal" class="form-label">Tanggal</label>
        <input type="date" id="tanggal" name="tanggal" class="form-control" value="<?= $tanggal ?>" required>
      </div>
      <div class="col-md-2 col-sm-6 align-self-end">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
      </div>
    </form>

    <!-- Total Pendapatan -->
    <div class="alert alert-success">
      <strong>Total Pendapatan Tanggal <?= date('d M Y', strtotime($tanggal)) ?>:</strong>
      Rp <?= number_format($totalHarian['total'] ?? 0, 0, ',', '.') ?>
    </div>

    <!-- Tabel Transaksi -->
    <div class="card">
      <div class="card-header bg-info text-white">
        <h3 class="card-title">Daftar Transaksi</h3>
      </div>
      <div class="card-body table-responsive p-0">
        <table class="table table-bordered table-hover table-striped mb-0">
          <thead class="table-light">
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>No HP</th>
              <th>Total</th>
              <th>DP</th>
              <th>Sisa</th>
              <th>Status</th>
              <th>Tanggal</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($data->num_rows > 0): $no = 1; ?>
              <?php while ($row = $data->fetch_assoc()): ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <a href="#" class="text-primary" data-toggle="modal" data-target="#modalDetail<?= $row['id'] ?>">
                      <?= htmlspecialchars($row['nama']) ?>
                    </a>
                  </td>
                  <td><?= htmlspecialchars($row['phone']) ?></td>
                  <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format($row['dp'], 0, ',', '.') ?></td>
                  <td>Rp <?= number_format($row['sisa'], 0, ',', '.') ?></td>
                  <td>
                    <?php if ($row['status_pembayaran'] === 'Lunas'): ?>
                      <span class="badge bg-success">Lunas</span>
                    <?php else: ?>
                      <span class="badge bg-warning text-dark">Belum Lunas</span>
                    <?php endif; ?>
                  </td>
                  <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                  <td>
                    <?php if ($row['status_pembayaran'] !== 'Lunas'): ?>
                      <form method="POST" class="d-flex flex-column flex-md-row gap-1">
                        <input type="hidden" name="bayar_id" value="<?= $row['id'] ?>">
                        <input type="number" name="nominal" class="form-control form-control-sm" placeholder="Nominal" required min="1">
                        <button type="submit" class="btn btn-success btn-sm mt-1 mt-md-0">Bayar</button>
                      </form>
                    <?php else: ?>
                      -
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr>
                <td colspan="9" class="text-center">Tidak ada transaksi hari ini.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

        <!-- Modal Detail -->
        <?php
        $data->data_seek(0);
        while ($row = $data->fetch_assoc()):
          $detail = $conn->query("
            SELECT i.nama, td.jumlah_hari, td.harga, td.subtotal
            FROM transaksi_detail td
            JOIN item i ON i.id = td.item_id
            WHERE td.transaksi_id = {$row['id']}
          ");
        ?>
        <div class="modal fade" id="modalDetail<?= $row['id'] ?>" tabindex="-1" aria-labelledby="modalLabel<?= $row['id'] ?>" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
              <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalLabel<?= $row['id'] ?>">Detail Item - <?= htmlspecialchars($row['nama']) ?></h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">

 <!-- ✅ Tambahan periode tanggal sewa -->
        <div class="mb-3">
          <strong>Periode Sewa:</strong><br>
          <?= date('d M Y', strtotime($row['tanggal_from'])) ?> s/d <?= date('d M Y', strtotime($row['tanggal_to'])) ?>
        </div>

                <table class="table table-bordered table-striped">
                  <thead class="table-light">
                    <tr>
                      <th>Nama Item</th>
                      <th>Jumlah Hari</th>
                      <th>Harga</th>
                      <th>Subtotal</th>
                    </tr>
                    
                  </thead>
                  <tbody>
                    <?php while ($d = $detail->fetch_assoc()): ?>
                      <tr>
                        <td><?= htmlspecialchars($d['nama']) ?></td>
                        <td><?= $d['jumlah_hari'] ?> hari</td>
                        <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                        <td>Rp <?= number_format($d['subtotal'], 0, ',', '.') ?></td>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </div>
  </div>
</section>

<?php include 'inc/footer.php'; ?>
