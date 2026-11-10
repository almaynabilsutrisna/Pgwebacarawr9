<?php
include 'koneksi.php';
$id = $_GET['id'];
$result = $conn->query("SELECT * FROM sebaran_cctv WHERE id = $id");
$row = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit CCTV Bantul</title>
    <!-- Anda bisa menempelkan CSS style dari index.php di sini agar seragam -->
    <style> body { font-family: sans-serif; padding: 20px; } .form-container { max-width: 500px; margin: auto; } input, select, button { width: 100%; padding: 10px; margin-bottom: 10px; } </style>
</head>
<body>
    <div class="form-container">
        <h2>Edit Data CCTV</h2>
        <form action="proses_edit.php" method="POST">
            <input type="hidden" name="id" value="<?= $row['id'] ?>">
            <label>Lokasi:</label><input type="text" name="lokasi" value="<?= $row['lokasi'] ?>" required>
            <label>Longitude:</label><input type="text" name="longitude" value="<?= $row['longitude'] ?>" required>
            <label>Latitude:</label><input type="text" name="latitude" value="<?= $row['latitude'] ?>" required>
            <label>Kategori:</label>
            <select name="kategori">
                <option value="Lalu Lintas" <?= ($row['kategori'] == 'Lalu Lintas') ? 'selected' : '' ?>>Lalu Lintas</option>
                <option value="Sungai & Bendungan" <?= ($row['kategori'] == 'Sungai & Bendungan') ? 'selected' : '' ?>>Sungai & Bendungan</option>
                <option value="Area Publik" <?= ($row['kategori'] == 'Area Publik') ? 'selected' : '' ?>>Area Publik</option>
            </select>
            <label>Status:</label>
            <select name="status">
                <option value="Aktif" <?= ($row['status'] == 'Aktif') ? 'selected' : '' ?>>Aktif</option>
                <option value="Offline" <?= ($row['status'] == 'Offline') ? 'selected' : '' ?>>Offline</option>
            </select>
            <button type="submit">Update Data</button>
            <a href="index.php" style="display:block; text-align:center; margin-top:10px;">Batal</a>
        </form>
    </div>
</body>
</html>