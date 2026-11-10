<?php
include 'koneksi.php';
$id = $_POST['id'];
$lokasi = $_POST['lokasi'];
$longitude = $_POST['longitude'];
$latitude = $_POST['latitude'];
$kategori = $_POST['kategori'];
$status = $_POST['status'];

$sql = "UPDATE sebaran_cctv SET 
        lokasi='$lokasi', longitude='$longitude', latitude='$latitude', kategori='$kategori', status='$status' 
        WHERE id=$id";

if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
} else {
    echo "Error updating record: " . $conn->error;
}
$conn->close();
?>