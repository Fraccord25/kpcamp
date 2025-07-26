<?php
$host = 'localhost';
$user = 'root';       // Ganti jika bukan root
$pass = '';           // Ganti jika ada password
$db   = 'kpcamp';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die('Koneksi ke database gagal: ' . $conn->connect_error);
}
?>
