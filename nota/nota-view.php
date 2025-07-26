<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}

include 'inc/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$trx = $conn->query("SELECT * FROM transaksi WHERE id = $id")->fetch_assoc();
$items = $conn->query("SELECT * FROM detail_transaksi WHERE transaksi_id = $id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Nota Transaksi - KP CAMP</title>
  <link rel="stylesheet" href="nota-style.css"> <!-- Gaya nota seperti nota.html -->
</head>
<body>
  <div class="page" role="main">
    <!-- Header -->
    <header>
      <div class="header-left">
        <div class="logo" aria-label="KP CAMP logo">
          <svg viewBox="0 0 54 54" xmlns="http://www.w3.org/2000/svg">
            <g fill="#333">
              <path d="M27 0c-6.7 0-11 5.67-11 10.7 0 5.45 7.924 9.3 11 43.3 3.076-34 11-38.7 11-43.3C38 5.67 33.7 0 27 0z"/>
              <rect x="22.5" y="22" width="3" height="10" rx="1"/>
              <rect x="28.5" y="22" width="3" height="10" rx="1"/>
            </g>
          </svg>
          <div class="logo-text-small">Plan · Journey · Story</div>
        </div>
      </div>
      <div class="header-center">
        <h1 class="title">KP CAMP</h1>
        <div class="subtitle">www.sewatendacamping.site</div>
        <div class="contacts">
          Depok : 0882-1941-2342 || Tangsel : 0851-7503-3459 || Pamulang : 0831-5273-3198<br />
          Ciputat : 0857-7606-0347 || Bekasi : 0851-7303-7260 || Bogor : 0859-5941-4191
        </div>
      </div>
      <div class="header-right">NO. <?= $trx['id'] ?></div>
    </header>

    <hr class="to-line" />

    <!-- Informasi -->
    <div class="info-row">
      <div class="info-left">
        <label>Nama :</label>
        <input type="text" value="<?= htmlspecialchars($trx['nama']) ?>" readonly />
      </div>
      <div class="info-center">
        <label>From :</label>
        <input type="text" value="<?= $trx['tanggal_from'] ?>" readonly />
      </div>
      <div class="info-center">
        <label>To :</label>
        <input type="text" value="<?= $trx['tanggal_to'] ?>" readonly />
      </div>
      <div class="info-right">
        <label>Phone :</label>
        <input type="text" value="<?= htmlspecialchars($trx['phone']) ?>" readonly />
      </div>
    </div>

    <!-- Tabel Item -->
    <table>
      <thead>
        <tr>
          <th>No</th><th>Kategori</th><th>Item</th><th>Qty</th><th>Price</th><th>Day</th><th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; $grand = 0; while ($row = $items->fetch_assoc()): $grand += $row['total']; ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['kategori']) ?></td>
          <td><?= htmlspecialchars($row['item']) ?></td>
          <td><?= $row['qty'] ?></td>
          <td><?= $row['price'] ?></td>
          <td><?= $row['day'] ?></td>
          <td><?= $row['total'] ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Catatan & Total -->
    <div class="notes-totals">
      <div class="notes">
        <p><strong>Catatan :</strong></p>
        <p>1. Jika pengembalian alat melebihi batas waktu yang ditentukan maka dianggap memperpanjang sewa</p>
        <p>2. Menyewa berarti menyetujui syarat &amp; ketentuan</p>
      </div>
      <table class="totals">
        <tr><td>GrandTotal</td><td>Rp <?= number_format($grand, 0, ',', '.') ?></td></tr>
        <tr><td>DP</td><td>Rp <?= number_format($trx['dp'], 0, ',', '.') ?></td></tr>
        <tr><td>Sisa</td><td>Rp <?= number_format($trx['sisa'], 0, ',', '.') ?></td></tr>
      </table>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div class="signature-block">KP CAMP</div>
      <div class="signature-block">PENYEWA</div>
      <div class="social-block">
        <div class="social-links">
          <div class="social-item"><span>@sewatendacampingdepok</span></div>
          <div class="social-item"><span>@sewatendacampingtangsel</span></div>
          <div class="social-item"><span>@sewatendacampingpamulang.id</span></div>
          <div class="social-item"><span>@sewatendacampingciputat_id</span></div>
          <div class="social-item"><span>@sewatendacampingbekasi</span></div>
          <div class="social-item"><span>@sewatendacampingcibinong_</span></div>
          <div class="social-item"><span>TikTok: kpcampofficial</span></div>
          <div class="social-item"><span>YouTube: KPCAMP</span></div>
        </div>
      </div>
    </div>
  </div>

  <!-- html2pdf.js -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
  <script>
    window.onload = function () {
      setTimeout(() => {
        html2pdf().from(document.querySelector('.page')).save('nota-<?= $trx['id'] ?>.pdf');
      }, 500);
    };
  </script>
</body>
</html>
