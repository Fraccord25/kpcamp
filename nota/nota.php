<?php
include '../inc/db.php';

// Ambil ID transaksi dari URL
$id = $_GET['id'] ?? 0;
$q = $conn->query("SELECT * FROM transaksi WHERE id = $id");
$transaksi = $q->fetch_assoc();

$items = $conn->query("SELECT * FROM transaksi_detail WHERE transaksi_id = $id");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>KP CAMP Invoice</title>
  <link rel="stylesheet" href="../assets/css/nota-style.css" />
</head>
<body>
  <div class="page" role="main">
    <header>
      <div class="header-left">
        <div class="logo" aria-label="KP CAMP logo">
          <svg viewBox="0 0 54 54" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
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

      <div class="header-right">No. <?= $id ?></div>
    </header>

    <hr class="to-line" />

    <div class="info-row">
      <div class="info-left"><strong>Nama:</strong> <?= $transaksi['nama'] ?></div>
      <div class="info-center"><strong>From:</strong> <?= $transaksi['tanggal_from'] ?></div>
      <div class="info-center"><strong>To:</strong> <?= $transaksi['tanggal_to'] ?></div>
      <div class="info-right"><strong>Phone:</strong> <?= $transaksi['phone'] ?></div>
    </div>

    <table>
      <thead>
        <tr>
          <th>No</th><th>Kategori</th><th>Item</th><th>Qty</th><th>Harga</th><th>Hari</th><th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; $grand = 0; foreach ($items as $i): 
          $total = $i['qty'] * $i['harga'] * $i['hari'];
          $grand += $total;
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= $i['kategori'] ?></td>
          <td><?= $i['item'] ?></td>
          <td><?= $i['qty'] ?></td>
          <td><?= $i['harga'] ?></td>
          <td><?= $i['hari'] ?></td>
          <td><?= $total ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="notes-totals">
      <div class="notes">
        <p><strong>Catatan :</strong></p>
        <p>1. Jika pengembalian alat melebihi batas waktu yang ditentukan maka dianggap memperpanjang sewa</p>
        <p>2. Menyewa berarti menyetujui syarat &amp; ketentuan</p>
      </div>
      <table class="totals">
        <tbody>
          <tr><td>Grand Total</td><td>Rp <?= number_format($grand) ?></td></tr>
          <tr><td>DP</td><td>Rp <?= number_format($transaksi['dp']) ?></td></tr>
          <tr><td>Sisa</td><td>Rp <?= number_format($transaksi['sisa']) ?></td></tr>
        </tbody>
      </table>
    </div>

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

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
  <script>
    setTimeout(() => {
      html2pdf().from(document.querySelector(".page")).save('nota-<?= $id ?>.pdf');
    }, 1000);
  </script>
</body>
</html>
