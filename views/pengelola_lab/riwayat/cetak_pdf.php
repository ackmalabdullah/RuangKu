<?php
session_start();

// Load koneksi
$koneksi_path = __DIR__ . '/../../../settings/koneksi.php';
require $koneksi_path;

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

// Query riwayat peminjaman lab
$query = "
    SELECT 
        p.id_peminjaman,
        m.nama AS nama_mahasiswa,
        l.nama_lab,
        p.tanggal_pinjam,
        p.jam_mulai,
        p.jam_selesai,
        p.keperluan,
        p.jumlah_peserta,
        p.status,
        p.created_at
    FROM peminjaman p
    LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa
    LEFT JOIN laboratorium l ON p.lab_id = l.id_lab
    WHERE p.lab_id IS NOT NULL
    ORDER BY p.created_at DESC
";

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<title>Laporan Riwayat Peminjaman Laboratorium</title>

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
        <h1>LAPORAN RIWAYAT PEMINJAMAN LABORATORIUM</h1>
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
                <th>Mahasiswa</th>
                <th>Laboratorium</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Keperluan</th>
                <th>Peserta</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        <?php
        $no = 1;
        while ($d = mysqli_fetch_assoc($result)) {

            $waktu = $d['jam_mulai'] . " - " . $d['jam_selesai'];
            $status = ucfirst($d['status']);

            echo "
            <tr>
                <td>{$no}</td>
                <td>{$d['nama_mahasiswa']}</td>
                <td>{$d['nama_lab']}</td>
                <td>{$d['tanggal_pinjam']}</td>
                <td>{$waktu}</td>
                <td>{$d['keperluan']}</td>
                <td>{$d['jumlah_peserta']}</td>
                <td>{$status}</td>
            </tr>";
            $no++;
        }

        // Jika kosong
        if ($no == 1) {
            echo "
            <tr>
                <td colspan='8' class='text-center'>Tidak ada data peminjaman.</td>
            </tr>";
        }
        ?>
        </tbody>
    </table>

</div>

<script>
    const now = new Date();
    const d = String(now.getDate()).padStart(2, '0');
    const m = String(now.getMonth()+1).padStart(2, '0');
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
