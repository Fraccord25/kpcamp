<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';
include 'inc/header.php';

// Periode filter
$periode = $_GET['periode'] ?? 'harian';

switch($periode) {
  case 'mingguan':
    $labelField = "DATE(created_at)";
    $groupBy = "created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    break;
  case 'bulanan':
    $labelField = "DATE_FORMAT(created_at,'%Y-%m')";
    $groupBy = "created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    break;
  case 'tahunan':
    $labelField = "YEAR(created_at)";
    $groupBy = "created_at >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    break;
  default:
    $labelField = "DATE(created_at)";
    $groupBy = "DATE(created_at) = CURDATE()";
}

// Ambil data grafik
$sqlCharts = $conn->query("
  SELECT $labelField AS label,
         SUM(dp + sisa) AS pemasukan
  FROM transaksi
  WHERE $groupBy
  GROUP BY label
  ORDER BY label
");

$labels = []; $chartData = [];
while($r = $sqlCharts->fetch_assoc()) {
  $labels[] = $r['label'];
  $chartData[] = (int)$r['pemasukan'];
}

// Total nota dan pemasukan
$totalNota = $conn->query("SELECT COUNT(*) AS total FROM transaksi")->fetch_assoc()['total'];
$totalPendapatan = $conn->query("SELECT SUM(dp + sisa) AS total FROM transaksi")->fetch_assoc()['total'] ?? 0;
?>

<div class="container-fluid py-4">
  <h3 class="mb-3">Dashboard KP CAMP</h3>

  <div class="row mb-4">
    <div class="col-md-6 col-12 mb-3">
      <div class="alert alert-primary">Total Nota: <strong><?= $totalNota ?></strong></div>
    </div>
    <div class="col-md-6 col-12 mb-3">
      <div class="alert alert-success">Total Pendapatan: <strong>Rp <?= number_format($totalPendapatan,0,',','.') ?></strong></div>
    </div>
  </div>

  <!-- Filter dropdown -->
  <form method="GET" class="mb-4">
    <div class="row">
      <div class="col-md-4 col-12">
        <label for="periode" class="form-label">Filter Pemasukan</label>
        <select name="periode" id="periode" class="form-select" onchange="this.form.submit()">
          <option value="harian" <?= $periode === 'harian' ? 'selected' : '' ?>>Harian</option>
          <option value="mingguan" <?= $periode === 'mingguan' ? 'selected' : '' ?>>Mingguan</option>
          <option value="bulanan" <?= $periode === 'bulanan' ? 'selected' : '' ?>>Bulanan</option>
          <option value="tahunan" <?= $periode === 'tahunan' ? 'selected' : '' ?>>Tahunan</option>
        </select>
      </div>
    </div>
  </form>

  <!-- Bar Chart -->
  <div class="card mb-4">
    <div class="card-header bg-info text-white">
      <h5 class="card-title mb-0">Pemasukan <?= ucfirst($periode) ?></h5>
    </div>
    <div class="card-body">
      <canvas id="barChart" style="min-height: 300px;"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('barChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Pemasukan (DP + Sisa)',
      data: <?= json_encode($chartData) ?>,
      backgroundColor: '#19875466',
      borderColor: '#198754',
      borderWidth: 1
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          callback: function(value) {
            return 'Rp ' + value.toLocaleString('id-ID');
          }
        }
      }
    },
    plugins: {
      legend: { display: false }
    }
  }
});
</script>

<?php include 'inc/footer.php'; ?>
