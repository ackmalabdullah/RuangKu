<?php
session_start();

// Load koneksi
$koneksi_path = __DIR__ . '/../../../settings/koneksi.php';
require $koneksi_path;

$database = new Database();
$conn = $database->conn;

// Ambil user login
$user_nama  = $_SESSION['nama_user'] ?? 'Pengelola Ruangan';
$user_email = $_SESSION['email'] ?? '-';

// Query fasilitas ruangan
$query = "
    SELECT 
    r.id_ruangan AS id_ruangan,
    r.nama_ruangan,
    f.nama_fasilitas,
    fr.jumlah
    FROM ruangan_fasilitas fr
    LEFT JOIN ruangan r ON fr.ruangan_id = r.id_ruangan
    LEFT JOIN fasilitas f ON fr.fasilitas_id = f.id_fasilitas
    ORDER BY r.nama_ruangan ASC, f.nama_fasilitas ASC;

";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Laporan Fasilitas Ruangan</title>

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background: white;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 950px;
            margin: auto;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header img {
            width: 90px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 22px;
            color: #005eff;
            margin-bottom: 2px;
        }

        .line {
            border-bottom: 3px solid #007bff;
            margin: 10px 0 25px 0;
        }

        .info-box {
            padding: 15px;
            background: #f7f9fc;
            border-left: 4px solid #007bff;
            margin-bottom: 25px;
            border-radius: 4px;
        }

        .info-box p {
            margin: 4px 0;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
        }

        th {
            background: #007bff;
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }

        tr:nth-child(even) {
            background: #f4faff;
        }
    </style>

</head>

<body>

    <div class="container">

        <div class="header">
            <img src="../../../assets/img/logo_kotak.png">
            <h1>LAPORAN FASILITAS RUANGAN</h1>
            <div>RuangKu Management System</div>
        </div>

        <div class="line"></div>

        <div class="info-box">
            <p><strong>User Login:</strong> <?= htmlspecialchars($user_nama) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user_email) ?></p>
            <p><strong>Tanggal Cetak:</strong> <span id="tanggal"></span></p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Ruangan</th>
                    <th>Fasilitas</th>
                    <th>Jumlah</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $no = 1;
                while ($d = mysqli_fetch_assoc($result)) {

                    echo "
            <tr>
                <td>{$no}</td>
                <td>{$d['nama_ruangan']}</td>
                <td>{$d['nama_fasilitas']}</td>
                <td>{$d['jumlah']}</td>
            </tr>";
                    $no++;
                }

                if ($no == 1) {
                    echo "
            <tr>
                <td colspan='4' class='text-center'>Tidak ada data fasilitas ruangan.</td>
            </tr>";
                }
                ?>
            </tbody>
        </table>

    </div>

    <script>
        const now = new Date();
        const d = String(now.getDate()).padStart(2, '0');
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const y = now.getFullYear();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');

        document.getElementById('tanggal').innerText =
            `${d}-${m}-${y} ${hh}:${mm}:${ss}`;

        window.onload = () => window.print();
    </script>

</body>

</html>