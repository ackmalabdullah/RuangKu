<?php
session_start();

$koneksi_path = __DIR__ . '../../../../settings/koneksi.php';

if (!file_exists($koneksi_path)) {
    die('ERROR: File koneksi.php tidak ditemukan di: ' . $koneksi_path);
}
require $koneksi_path;

$database = new Database();
$conn = $database->conn;

// Ambil data lab
$query = mysqli_query($conn, "SELECT id_lab, nama_lab, lokasi, kapasitas, status_lab, created_at 
                              FROM laboratorium 
                              ORDER BY nama_lab ASC");

if (!$query) {
    die('ERROR Query: ' . mysqli_error($conn));
}

if (mysqli_num_rows($query) == 0) {
    $rows = '<tr><td colspan="6" style="text-align: center;">Belum ada data lab</td></tr>';
} else {
    $rows = '';
    $no = 1;
    while ($data = mysqli_fetch_assoc($query)) {
        $tanggal = !empty($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : 'N/A';

        $rows .= '
        <tr>
            <td style="text-align:center;">'.$no++.'</td>
            <td>'.htmlspecialchars($data['nama_lab']).'</td>
            <td>'.htmlspecialchars($data['lokasi']).'</td>
            <td style="text-align:center;">'.$data['kapasitas'].'</td>
            <td>'.htmlspecialchars($data['status_lab']).'</td>
            <td>'.$tanggal.'</td>
        </tr>';
    }
}

$user_nama = $_SESSION['nama_user'] ?? 'Admin';
$user_email = $_SESSION['email'] ?? '-';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak PDF - Laporan Data Lab</title>
    <style>
        * { margin: 0; padding: 0; }
        body {
            font-family: 'Montserrat', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 1000px;
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
        .header-logo img {
            width: 80px;
            height: auto;
        }
        .header-content {
            flex: 1;
            text-align: center;
        }
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
        .info-box p {
            margin: 8px 0;
            font-size: 13px;
        }
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
        }
        table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        @media print {
            body { margin: 0; padding: 0; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="header-logo">
            <img src="../../../assets/img/logo kotak.png">
        </div>
        <div class="header-content">
            <h1>LAPORAN DATA LABORATORIUM</h1>
            <p>RuangKu Management System</p>
        </div>
    </div>

    <div class="info-box">
        <p><strong>User Login:</strong> <?= htmlspecialchars($user_nama) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
        <p><strong>Tanggal Cetak:</strong> <span id="tanggal-cetak"></span></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Lab</th>
                <th>Lokasi</th>
                <th width="80">Kapasitas</th>
                <th width="150">Status</th>
                <th width="200">Tanggal Input</th>
            </tr>
        </thead>
        <tbody>
            <?= $rows ?>
        </tbody>
    </table>
</div>

<script>
function updateTanggalCetak() {
    const now = new Date();
    const tgl = now.toLocaleString('id-ID', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    });
    document.getElementById('tanggal-cetak').textContent = tgl;
}
updateTanggalCetak();
setInterval(updateTanggalCetak, 1000);

window.onload = function() {
    window.print();
};
</script>

</body>
</html>
