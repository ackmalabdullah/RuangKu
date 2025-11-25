<?php
session_start();

// Path koneksi
$koneksi_path = __DIR__ . '/../../../settings/koneksi.php';

if (!file_exists($koneksi_path)) {
    die('ERROR: File koneksi.php tidak ditemukan di: ' . $koneksi_path);
}
require $koneksi_path;

// Koneksi Database
$database = new Database();
$conn = $database->conn;

// Query peminjaman
$query = mysqli_query($conn, "
    SELECT p.*,
           m.nama AS nama_peminjam,
           l.nama_lab
    FROM peminjaman p
    LEFT JOIN mahasiswa m ON m.id_mahasiswa = p.mahasiswa_id
    LEFT JOIN laboratorium l ON l.id_lab = p.lab_id
    ORDER BY p.id_peminjaman DESC
");

if (!$query) {
    die('ERROR Query: ' . mysqli_error($conn));
}

// Generate Rows
$rows = '';
if (mysqli_num_rows($query) == 0) {

    $rows .= '
        <tr>
            <td colspan="7" style="text-align:center;">Belum ada data peminjaman</td>
        </tr>
    ';

} else {

    $no = 1;

    while ($data = mysqli_fetch_assoc($query)) {

        // Pastikan nilai aman
        $tanggal_pinjam = $data['tanggal_pinjam'] ?? '-';
        $tanggal_kembali = (!empty($data['tanggal_kembali'])) ? $data['tanggal_kembali'] : '-';
        $status = $data['status'] ?? '-';
        $keperluan = $data['keperluan'] ?? '-';

        $rows .= '
        <tr>
            <td style="text-align:center;">'.$no++.'</td>
            <td>'.htmlspecialchars($data['nama_peminjam']).'</td>
            <td>'.htmlspecialchars($data['nama_lab']).'</td>
            <td>'.$tanggal_pinjam.'</td>
            <td>'.$tanggal_kembali.'</td>
            <td>'.$status.'</td>
            <td>'.$keperluan.'</td>
        </tr>
        ';
    }
}

// User Info
$user_nama  = $_SESSION['nama_user'] ?? 'Admin';
$user_email = $_SESSION['email'] ?? '-';

?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Cetak PDF - Laporan Peminjaman Lab</title>

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
            margin: 6px 0;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: white;
        }

        table th {
            background-color: #007bff;
            color: white;
            padding: 10px;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        @media print {
            table { page-break-inside: avoid; }
        }
    </style>
</head>

<body>

    <div class="container">

        <!-- Header -->
        <div class="header">
            <div class="header-logo">
                <img src="../../../assets/img/logo kotak.png">
            </div>

            <div class="header-content">
                <h1>LAPORAN PEMINJAMAN LABORATORIUM</h1>
                <p>RuangKu Management System</p>
            </div>
        </div>

        <!-- Info User -->
        <div class="info-box">
            <p><strong>User Login:</strong> <?= htmlspecialchars($user_nama) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
            <p><strong>Tanggal Cetak:</strong> <span id="tanggal-cetak"></span></p>
        </div>

        <!-- Tabel -->
        <table>
            <thead>
                <tr>
                    <th width="40">No</th>
                    <th width="150">Peminjam</th>
                    <th width="150">Lab</th>
                    <th width="120">Tgl Pinjam</th>
                    <th width="120">Tgl Kembali</th>
                    <th width="100">Status</th>
                    <th>Keperluan</th>
                </tr>
            </thead>
            <tbody>
                <?= $rows ?>
            </tbody>
        </table>

    </div>

    <script>
        // Tanggal real-time
        function updateTanggalCetak() {
            const now = new Date();
            const tgl = now.toLocaleString('id-ID', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('tanggal-cetak').textContent = tgl;
        }

        updateTanggalCetak();
        setInterval(updateTanggalCetak, 1000);

        // Auto print
        window.onload = () => window.print();
    </script>

</body>
</html>
