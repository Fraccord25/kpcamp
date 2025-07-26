<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';
include 'inc/header.php';

// Harian
$today = date('Y-m-d');
$harian = $conn->query("SELECT HOUR(created_at) as jam, COUNT(*) as jumlah FROM transaksi WHERE DATE(created_at) = '$today' GROUP BY jam");
$labelsHarian = []; $dataHarian = [];
while ($row = $harian->fetch_assoc()) {
  $labelsHarian[] = $row['jam'] . ":00";
  $dataHarian[] = (int)$row['jumlah'];
}

// Mingguan
$mingguan = $conn->query("SELECT DATE(created_at) as tanggal, COUNT(*) as jumlah FROM transaksi WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) GROUP BY tanggal");
$labelsMingguan = []; $dataMingguan = [];
while ($row = $mingguan->fetch_assoc()) {
  $labelsMingguan[] = $row['tanggal'];
  $dataMingguan[] = (int)$row['jumlah'];
}

// Bulanan
$bulanan = $conn->query("SELECT DATE(created_at) as tanggal, COUNT(*) as jumlah FROM transaksi WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) GROUP BY tanggal");
$labelsBulanan = []; $dataBulanan = [];
while ($row = $bulanan->fetch_assoc()) {
  $labelsBulanan[] = $row['tanggal'];
  $dataBulanan[] = (int)$row['jumlah'];
}

// Total Nota dan Pendapatan
$totalNota = $conn->query("SELECT COUNT(*) as total FROM transaksi")->fetch_assoc()['total'];
$totalPendapatan = $conn->query("SELECT SUM(dp) as total FROM transaksi")->fetch_assoc()['total'] ?? 0;

// Chart Item Sewa Mingguan
$itemMingguan = $conn->query("
  SELECT DATE(t.created_at) as tanggal, SUM(td.jumlah_hari) as total_item
  FROM transaksi t
  JOIN transaksi_detail td ON t.id = td.transaksi_id
  WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
  GROUP BY tanggal
");
$labelItemMingguan = []; $dataItemMingguan = [];
while ($row = $itemMingguan->fetch_assoc()) {
  $labelItemMingguan[] = $row['tanggal'];
  $dataItemMingguan[] = (int)$row['total_item'];
}
?>

<div class="container-fluid py-4">
  <h3 class="mb-4">Dashboard KP CAMP</h3>

  <!-- Info Box -->
  <div class="row mb-4">
    <div class="col-md-6 col-12 mb-3">
      <div class="alert alert-primary">
        Total Nota: <strong><?= $totalNota ?></strong>
      </div>
    </div>
    <div class="col-md-6 col-12 mb-3">
      <div class="alert alert-success">
        Total Pendapatan (DP): <strong>Rp <?= number_format($totalPendapatan, 0, ',', '.') ?></strong>
      </div>
    </div>
  </div>

  <!-- Chart -->
  <div class="row">
    <div class="col-md-6 col-12 mb-4">
      <div class="card">
        <div class="card-header bg-primary text-white">Nota Harian</div>
        <div class="card-body">
          <canvas id="chartHarian" style="min-height: 250px;"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-12 mb-4">
      <div class="card">
        <div class="card-header bg-info text-white">Nota Mingguan</div>
        <div class="card-body">
          <canvas id="chartMingguan" style="min-height: 250px;"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-12 mb-4">
      <div class="card">
        <div class="card-header bg-warning">Nota Bulanan</div>
        <div class="card-body">
          <canvas id="chartBulanan" style="min-height: 250px;"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6 col-12 mb-4">
      <div class="card">
        <div class="card-header bg-secondary text-white">Item Sewa Mingguan</div>
        <div class="card-body">
          <canvas id="chartItemMingguan" style="min-height: 250px;"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const configChart = (id, labels, data, label, color) => {
  new Chart(document.getElementById(id), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: label,
        data: data,
        borderColor: color,
        backgroundColor: color + '33',
        fill: true,
        tension: 0.4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: { y: { beginAtZero: true } },
      plugins: { legend: { display: true } }
    }
  });
};

configChart('chartHarian', <?= json_encode($labelsHarian) ?>, <?= json_encode($dataHarian) ?>, 'Nota Harian', '#0d6efd');
configChart('chartMingguan', <?= json_encode($labelsMingguan) ?>, <?= json_encode($dataMingguan) ?>, 'Nota Mingguan', '#198754');
configChart('chartBulanan', <?= json_encode($labelsBulanan) ?>, <?= json_encode($dataBulanan) ?>, 'Nota Bulanan', '#ffc107');
configChart('chartItemMingguan', <?= json_encode($labelItemMingguan) ?>, <?= json_encode($dataItemMingguan) ?>, 'Item Sewa', '#6c757d');
</script>

<?php include 'inc/footer.php'; ?>
