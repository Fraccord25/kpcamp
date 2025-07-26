<?php
include 'inc/db.php';

$id = 1; // ID admin yang mau diubah
$nama = "Admin Baru";
$email = "adminbaru@kpcamp.local";
$password = md5("123456");

$sql = "UPDATE admin SET nama='$nama', email='$email', password='$password' WHERE id=$id";
if ($conn->query($sql) === TRUE) {
  echo "Admin berhasil diubah.";
} else {
  echo "Gagal mengubah admin: " . $conn->error;
}
?>
