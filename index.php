<?php 
include 'koneksi.php'; 

// Proses Insert Data Baru
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $lokasi = $_POST['lokasi'];
    $longitude = $_POST['longitude'];
    $latitude = $_POST['latitude'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];

    $sql = "INSERT INTO sebaran_cctv (lokasi, longitude, latitude, kategori, status) 
            VALUES ('$lokasi', '$longitude', '$latitude', '$kategori', '$status')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Data CCTV berhasil ditambahkan!'); window.location.href='index.php';</script>";
        exit();
    }
}

// Query untuk Statistik Dashboard
$count_all = $conn->query("SELECT COUNT(*) as total FROM sebaran_cctv")->fetch_assoc()['total'];
$count_aktif = $conn->query("SELECT COUNT(*) as aktif FROM sebaran_cctv WHERE status='Aktif'")->fetch_assoc()['aktif'];
$count_offline = $conn->query("SELECT COUNT(*) as offline FROM sebaran_cctv WHERE status='Offline'")->fetch_assoc()['offline'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>WebGIS CCTV Bantul</title>
    
    <!-- Library Leaflet CSS untuk Peta Interaktif -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    
    <style>
        :root { --primary: #0284c7; --primary-hover: #0369a1; --danger: #ef4444; --warning: #f59e0b; --success: #10b981; --bg: #f8fafc; --surface: #ffffff; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--bg); margin: 0; padding: 30px; display: flex; flex-direction: column; align-items: center; }
        .container { width: 100%; max-width: 1100px; }
        h2 { text-align: center; color: #1e293b; font-size: 28px; margin-bottom: 5px; }
        p.subtitle { text-align: center; color: #64748b; margin-bottom: 30px; }
        
        /* Widget Statistik */
        .stats-container { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-card { flex: 1; background: var(--surface); padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid var(--primary); }
        .stat-card h4 { margin: 0; color: #64748b; font-size: 15px; text-transform: uppercase; }
        .stat-card h1 { margin: 10px 0 0 0; font-size: 36px; color: #1e293b; }
        .stat-aktif { border-bottom-color: var(--success); }
        .stat-offline { border-bottom-color: #94a3b8; }

        /* Peta Leaflet */
        #map { height: 400px; width: 100%; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 30px; z-index: 1; border: 2px solid #e2e8f0; }

        /* Gaya Tabel & Form (Seperti sebelumnya) */
        .table-wrapper { background: var(--surface); border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 30px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: center; border-bottom: 1px solid #e2e8f0; }
        th { background: #f1f5f9; color: #475569; font-size: 14px; text-transform: uppercase; }
        tr:hover { background: #f8fafc; }
        .badge { padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; color: white; }
        .badge-aktif { background: var(--success); }
        .badge-offline { background: #64748b; }
        .btn { padding: 8px 12px; border-radius: 6px; text-decoration: none; color: white; font-size: 13px; font-weight: bold; margin: 0 2px;}
        .btn-edit { background: var(--warning); }
        .btn-delete { background: var(--danger); }
        .form-container { background: var(--surface); padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; color: #334155; }
        input, select { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: var(--primary); color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-top: 10px; font-size: 16px;}
        button:hover { background: var(--primary-hover); }
    </style>
</head>
<body>

<div class="container">
    <h2>📡 Dashboard Geoportal CCTV Bantul</h2>
    <p class="subtitle">Sistem Informasi Geografis Pemantauan Lalu Lintas dan Area Publik</p>
    
    <!-- Widget Statistik -->
    <div class="stats-container">
        <div class="stat-card">
            <h4>Total Titik CCTV</h4>
            <h1><?= $count_all ?></h1>
        </div>
        <div class="stat-card stat-aktif">
            <h4>CCTV Aktif</h4>
            <h1 style="color: var(--success);"><?= $count_aktif ?></h1>
        </div>
        <div class="stat-card stat-offline">
            <h4>CCTV Offline</h4>
            <h1 style="color: #64748b;"><?= $count_offline ?></h1>
        </div>
    </div>

    <!-- Container untuk Peta -->
    <div id="map"></div>

    <!-- Tabel Data -->
    <div class="table-wrapper">
        <table>
            <tr>
                <th>ID</th>
                <th>Lokasi</th>
                <th>Kategori</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
            <?php
            $sql = "SELECT * FROM sebaran_cctv";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $statusBadge = ($row['status'] == 'Aktif') ? 'badge-aktif' : 'badge-offline';
                    echo "<tr>
                            <td>" . $row["id"]. "</td>
                            <td><strong>📍 " . $row["lokasi"]. "</strong><br><small style='color:#64748b;'>".$row["latitude"].", ".$row["longitude"]."</small></td>
                            <td>" . $row["kategori"]. "</td>
                            <td><span class='badge $statusBadge'>" . $row["status"]. "</span></td>
                            <td>
                                <a href='edit.php?id=" . $row["id"] . "' class='btn btn-edit'>Edit</a>
                                <a href='delete.php?id=" . $row["id"] . "' class='btn btn-delete' onclick='return confirm(\"Yakin ingin menghapus titik CCTV ini?\")'>Hapus</a>
                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada data CCTV</td></tr>"; 
            }
            ?>
        </table>
    </div>

    <!-- Form Tambah Data -->
    <div class="form-container">
        <h3 style="text-align:center; color:#1e293b; margin-top:0;">Tambah Titik CCTV Baru</h3>
        <form action="" method="POST">
            <div class="form-group"><label>Nama Lokasi / Simpang:</label><input type="text" name="lokasi" required></div>
            <div class="form-group"><label>Longitude (X):</label><input type="text" name="longitude" required></div>
            <div class="form-group"><label>Latitude (Y):</label><input type="text" name="latitude" required></div>
            <div class="form-group">
                <label>Kategori Wilayah:</label>
                <select name="kategori">
                    <option value="Lalu Lintas">Lalu Lintas</option>
                    <option value="Sungai & Bendungan">Sungai & Bendungan</option>
                    <option value="Area Publik">Area Publik</option>
                </select>
            </div>
            <div class="form-group">
                <label>Status CCTV:</label>
                <select name="status">
                    <option value="Aktif">Aktif</option>
                    <option value="Offline">Offline</option>
                </select>
            </div>
            <button type="submit">Simpan ke Database</button>
        </form>
    </div>
</div>

<!-- Library Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Script Inisialisasi Peta WebGIS -->
<script>
    // 1. Inisialisasi Peta, Set View awal ke koordinat tengah Bantul
    var map = L.map('map').setView([-7.8872, 110.3283], 11);

    // 2. Load Basemap dari OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors | WebGIS Bantul'
    }).addTo(map);

    // 3. Memasukkan Data dari PHP ke dalam Javascript
    <?php
    $map_data = $conn->query("SELECT * FROM sebaran_cctv");
    while($row = $map_data->fetch_assoc()) {
        $lat = $row['latitude'];
        $lng = $row['longitude'];
        $lokasi = addslashes($row['lokasi']);
        $status = $row['status'];
        $kategori = $row['kategori'];
        
        // Memunculkan Marker / Pin Peta
        echo "L.marker([$lat, $lng]).addTo(map)
        .bindPopup('<b>$lokasi</b><br>Kategori: $kategori<br>Status: $status');\n";
    }
    ?>
</script>

</body>
</html>
<?php $conn->close(); ?>