<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header("Location: login.php");
  exit();
}
include 'inc/db.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$message = "";
$itemList = $conn->query("SELECT id, nama, harga, kategori_id FROM item");

// Proses simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama = $_POST['nama'];
  $phone = $_POST['phone'];
  $from = $_POST['tanggal_from'];
  $to = $_POST['tanggal_to'];
  $total = (int)$_POST['total'];
  $dp = (int)$_POST['dp'];
  $sisa = $total - $dp;
  $status = ($sisa <= 0) ? 'Lunas' : 'DP';

  // Cek jika user sudah ada berdasarkan nomor telepon
  $checkUser = $conn->prepare("SELECT id FROM user WHERE phone = ?");
  $checkUser->bind_param("s", $phone);
  $checkUser->execute();
  $checkUser->store_result();

  if ($checkUser->num_rows === 0) {
    $emailDummy = $phone . "@kpcamp.local";
    $insertUser = $conn->prepare("INSERT INTO user (nama, phone, alamat, email, password) VALUES (?, ?, '', ?, '')");
    $insertUser->bind_param("sss", $nama, $phone, $emailDummy);
    $insertUser->execute();
  }

  // Simpan ke transaksi
  $stmt = $conn->prepare("INSERT INTO transaksi (nama, phone, tanggal_from, tanggal_to, total, dp, sisa, status_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("ssssiiis", $nama, $phone, $from, $to, $total, $dp, $sisa, $status);
  if ($stmt->execute()) {
    $transaksi_id = $stmt->insert_id;
    foreach ($_POST['item_id'] as $i => $item_id) {
      $jumlah_hari = $_POST['jumlah_hari'][$i];
      $harga = $_POST['harga'][$i];
      $subtotal = $_POST['subtotal'][$i];
      $stmt2 = $conn->prepare("INSERT INTO transaksi_detail (transaksi_id, item_id, jumlah_hari, harga, subtotal) VALUES (?, ?, ?, ?, ?)");
      $stmt2->bind_param("iiiii", $transaksi_id, $item_id, $jumlah_hari, $harga, $subtotal);
      $stmt2->execute();
    }
    header("Location: progress.php?success=1");
    exit();
  } else {
    $message = "<div class='alert alert-danger'>Gagal menyimpan data.</div>";
  }
}

include 'inc/header.php';


?>

<!-- Main Content -->
<section class="content">
  <div class="container-fluid py-3">
    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <strong>Berhasil!</strong> Nota berhasil disimpan.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
  </div>
<?php endif; ?>

    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Input Nota Sewa</h3>
      </div>
      <div class="card-body">
        <?= $message ?>
        <form method="POST" id="notaForm">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label>Nama Penyewa</label>
              <input type="text" name="nama" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>No. Telepon</label>
              <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Tanggal Dari</label>
              <input type="date" name="tanggal_from" id="tanggal_from" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
              <label>Tanggal Sampai</label>
              <input type="date" name="tanggal_to" id="tanggal_to" class="form-control" required>
            </div>
          </div>

          <hr>

          <div class="mb-3 d-flex justify-content-between align-items-center flex-wrap">
            <div>
              <label>Jumlah Hari</label>
              <input type="number" id="inputHari" class="form-control" value="1" readonly style="max-width: 120px;">
            </div>
            <div class="mt-2 mt-md-0">
              <label>&nbsp;</label><br>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalItem">
                <i class="fas fa-plus-circle"></i> Tambah Item
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table class="table table-bordered" id="tabelItem">
              <thead class="table-light">
                <tr>
                  <th>Nama</th>
                  <th>Hari</th>
                  <th>Harga</th>
                  <th>Subtotal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody></tbody>
              <tfoot>
                <tr>
                  <th colspan="3">Total</th>
                  <th colspan="2" id="totalText">Rp 0</th>
                </tr>
              </tfoot>
            </table>
          </div>

          <input type="hidden" name="total" id="totalValue">

          <div class="row mb-3">
            <div class="col-md-4">
              <label>DP (Rp)</label>
              <input type="number" name="dp" class="form-control" id="dp" required>
            </div>
            <div class="col-md-4">
              <label>Sisa Bayar</label>
              <input type="text" class="form-control" id="sisa" readonly>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">Simpan Nota</button>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- Modal Tambah Item -->
<div class="modal fade" id="modalItem" tabindex="-1" aria-labelledby="modalItemLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalItemLabel"><i class="fas fa-box-open me-2"></i>Tambah Item Sewa</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <form id="modalItemForm">
          <div class="row g-3">
            <div class="col-md-6">
              <label for="modalKategori" class="form-label">Kategori</label>
              <select id="modalKategori" class="form-select" onchange="loadItemModal(this.value)">
                <option value="">Pilih Kategori</option>
                <?php
                $kategori = $conn->query("SELECT * FROM kategori");
                while($kat = $kategori->fetch_assoc()):
                ?>
                  <option value="<?= $kat['id'] ?>"><?= htmlspecialchars($kat['nama']) ?></option>
                <?php endwhile; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label for="modalItemSelect" class="form-label">Item</label>
              <select id="modalItemSelect" class="form-select">
                <option value="">Pilih Item</option>
              </select>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Batal
        </button>
        <button type="button" class="btn btn-primary" onclick="submitItemFromModal()">
          <i class="fas fa-plus-circle"></i> Tambahkan
        </button>
      </div>
    </div>
  </div>
</div>

<?php include 'inc/footer.php'; ?>

<script>
const itemData = <?= json_encode(iterator_to_array($itemList)) ?>;
let tableBody = document.querySelector('#tabelItem tbody');
let totalText = document.getElementById('totalText');
let totalValue = document.getElementById('totalValue');
let dpInput = document.getElementById('dp');
let sisaInput = document.getElementById('sisa');

function formatRupiah(angka) {
  return 'Rp ' + angka.toLocaleString('id-ID');
}

function loadItemModal(kategoriId) {
  const itemSelect = document.getElementById('modalItemSelect');
  itemSelect.innerHTML = '<option value="">Pilih Item</option>';
  itemData.forEach(item => {
    if (item.kategori_id == kategoriId) {
      const opt = document.createElement('option');
      opt.value = item.id;
      opt.dataset.harga = item.harga;
      opt.text = `${item.nama} - Rp${parseInt(item.harga).toLocaleString('id-ID')}`;
      itemSelect.appendChild(opt);
    }
  });
}

function submitItemFromModal() {
  const itemSelect = document.getElementById('modalItemSelect');
  const itemId = itemSelect.value;
  const itemName = itemSelect.options[itemSelect.selectedIndex]?.text;
  const harga = parseInt(itemSelect.options[itemSelect.selectedIndex]?.dataset.harga || 0);
  const hari = parseInt(document.getElementById('inputHari').value);

  if (!itemId || !harga || !hari) return;

  const subtotal = harga * hari;
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>${itemName}<input type="hidden" name="item_id[]" value="${itemId}"></td>
    <td>${hari}<input type="hidden" name="jumlah_hari[]" value="${hari}"></td>
    <td>${formatRupiah(harga)}<input type="hidden" name="harga[]" value="${harga}"></td>
    <td>${formatRupiah(subtotal)}<input type="hidden" name="subtotal[]" value="${subtotal}"></td>
    <td><button type="button" class="btn btn-sm btn-danger" onclick="hapusItem(this)"><i class="fas fa-trash"></i></button></td>
  `;
  tableBody.appendChild(row);
  updateTotal();
  bootstrap.Modal.getInstance(document.getElementById('modalItem')).hide();
}

function hapusItem(btn) {
  btn.closest('tr').remove();
  updateTotal();
}

function updateTotal() {
  let total = 0;
  document.querySelectorAll('input[name="subtotal[]"]').forEach(input => {
    total += parseInt(input.value);
  });
  totalText.textContent = formatRupiah(total);
  totalValue.value = total;
  updateSisa();
}

function updateSisa() {
  const total = parseInt(totalValue.value) || 0;
  const dp = parseInt(dpInput.value) || 0;
  sisaInput.value = formatRupiah(total - dp);
}

dpInput.addEventListener('input', updateSisa);

document.getElementById('tanggal_from').addEventListener('change', hitungHari);
document.getElementById('tanggal_to').addEventListener('change', hitungHari);

function hitungHari() {
  const from = new Date(document.getElementById('tanggal_from').value);
  const to = new Date(document.getElementById('tanggal_to').value);
  if (from && to && to >= from) {
    const selisih = Math.ceil((to - from) / (1000 * 60 * 60 * 24)) + 1;
    document.getElementById('inputHari').value = selisih;
  } else {
    document.getElementById('inputHari').value = 1;
  }
}
</script>
