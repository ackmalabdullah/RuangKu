<?php
session_start();

// ====== LOAD KONFIGURASI DATABASE ======
$koneksi_path = __DIR__ . '/../../../settings/koneksi.php';

if (!file_exists($koneksi_path)) {
    die('ERROR: File koneksi.php tidak ditemukan di: ' . $koneksi_path);
}
require $koneksi_path;

// Koneksi
$database = new Database();
$conn = $database->conn;

$id_user = $_SESSION['id_user'] ?? null;

$user_nama = 'Admin';
$user_email = '-';

if ($id_user) {
    $q = mysqli_query($conn, "SELECT nama, email FROM users WHERE id_user = '$id_user' LIMIT 1");
    if ($q && mysqli_num_rows($q) == 1) {
        $u = mysqli_fetch_assoc($q);
        $user_nama = $u['nama'];
        $user_email = $u['email'];
    }
}

if (!$conn) {
    die('ERROR: Koneksi database gagal!');
}

// ====== QUERY DATA LABORATORIUM + FASILITAS ======
$query = "
    SELECT 
        l.id_lab,
        l.nama_lab,
        l.lokasi,
        l.kapasitas,
        l.status_lab,
        l.gambar,
        kr.nama_kategori,

        GROUP_CONCAT(
            CONCAT(f.nama_fasilitas, ' (', lf.jumlah, ')')
            ORDER BY f.nama_fasilitas ASC
            SEPARATOR '<br>'
        ) AS daftar_fasilitas

    FROM laboratorium l
    LEFT JOIN kategori_ruangan kr ON l.kategori_id = kr.id_kategori
    LEFT JOIN lab_fasilitas lf ON l.id_lab = lf.lab_id
    LEFT JOIN fasilitas f ON lf.fasilitas_id = f.id_fasilitas

    GROUP BY 
        l.id_lab, l.nama_lab, l.lokasi, l.kapasitas, 
        l.status_lab, l.gambar, kr.nama_kategori

    ORDER BY l.nama_lab ASC
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die('ERROR Query: ' . mysqli_error($conn));
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak PDF - Laporan Data Laboratorium</title>

    <style>
        * { margin: 0; padding: 0; }
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 900px;
            margin: 20px auto;
            padding: 20px;
            background: white;
        }
        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
        }
        .header-logo {
            width: 80px;
            height: 80px;
            margin-right: 20px;
        }
        .header-logo img {
            width: 100%;
            height: auto;
        }
        .header-content { flex: 1; text-align: center; }
        .header h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .info-box {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 25px;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .info-box p { margin: 8px 0; font-size: 13px; }
        .info-box strong { color: #007bff; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background: white;
        }
        table th {
            background-color: #007bff;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        table tr:nth-child(even) { background-color: #f8f9fa; }
        table tr:hover { background-color: #e9ecef; }

        @media print {
            .container { margin: 0; padding: 0; }
            table { page-break-inside: avoid; }
        }
    </style>

</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="header-logo">
            <img src="../../../assets/img/logo kotak.png">
        </div>
        <div class="header-content">
            <h1>LAPORAN DATA LABORATORIUM</h1>
            <p>RuangKu Management System</p>
        </div>
    </div>

    <!-- INFO USER -->
    <div class="info-box">
        <p><strong>User Login:</strong> <?= htmlspecialchars($user_nama) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
        <p><strong>Tanggal Cetak:</strong> <span id="tanggal-cetak"></span></p>
    </div>

    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th width="40">No</th>
                <th>Nama Lab</th>
                <th>Kategori</th>
                <th>Lokasi</th>
                <th width="60">Kapasitas</th>
                <th>Fasilitas & Jumlah</th>
                <th width="100">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if (mysqli_num_rows($result) == 0) {
                echo '<tr><td colspan="7" style="text-align:center;">Belum ada data laboratorium</td></tr>';
            } else {
                $no = 1;
                while ($d = mysqli_fetch_assoc($result)) {
                    $status_label = str_replace('_', ' ', ucwords($d['status_lab']));
                    echo "
                    <tr>
                        <td>{$no}</td>
                        <td>".htmlspecialchars($d['nama_lab'])."</td>
                        <td>".htmlspecialchars($d['nama_kategori'] ?? 'Tanpa Kategori')."</td>
                        <td>".htmlspecialchars($d['lokasi'])."</td>
                        <td>".htmlspecialchars($d['kapasitas'])."</td>
                        <td>".($d['daftar_fasilitas'] ?: '<i>Tidak ada fasilitas</i>')."</td>
                        <td>{$status_label}</td>
                    </tr>";
                    $no++;
                }
            }
            ?>
        </tbody>
    </table>

</div>

<script>
// Format tanggal realtime
function updateTanggalCetak() {
    const now = new Date();
    const d = String(now.getDate()).padStart(2, '0');
    const m = String(now.getMonth()+1).padStart(2, '0');
    const y = now.getFullYear();
    const hh = String(now.getHours()).padStart(2, '0');
    const mm = String(now.getMinutes()).padStart(2, '0');
    const ss = String(now.getSeconds()).padStart(2, '0');

    document.getElementById('tanggal-cetak').textContent =
        `${d}-${m}-${y} ${hh}:${mm}:${ss}`;
}

updateTanggalCetak();
setInterval(updateTanggalCetak, 1000);

// Auto print
window.onload = function() {
    window.print();
};
</script>

</body>
</html>
