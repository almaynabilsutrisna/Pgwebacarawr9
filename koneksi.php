<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "latihan_pgweb"; // Sesuaikan dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>