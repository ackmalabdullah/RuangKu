<?php
session_start();

$koneksi_path = __DIR__ . '../../../../settings/koneksi.php';

if (!file_exists($koneksi_path)) {
    die('ERROR: File koneksi.php tidak ditemukan di: ' . $koneksi_path);
}
require $koneksi_path;

$database = new Database();
$conn = $database->conn;

if (!$conn) {
    die('ERROR: Koneksi database gagal!');
}

/* ============================
   AMBIL DATA USER LOGIN
=============================== */
$id_user = $_SESSION['id_user'] ?? null;

$user_nama = 'Admin';
$user_email = '-';

if ($id_user) {
    $getUser = mysqli_query($conn, "SELECT nama, email FROM users WHERE id_user = '$id_user' LIMIT 1");

    if ($getUser && mysqli_num_rows($getUser) == 1) {
        $u = mysqli_fetch_assoc($getUser);
        $user_nama = $u['nama'];
        $user_email = $u['email'];
    }
}

/* ============================
   AMBIL DATA PENGELOLA
=============================== */
$query = mysqli_query($conn, "
    SELECT id_user, nama, email, username, role, created_at
    FROM users
    WHERE role = 'pengelola_ruangan' 
       OR role = 'pengelola_lab'
    ORDER BY nama ASC
");

if (!$query) {
    die('ERROR Query: ' . mysqli_error($conn));
}

$rows = '';
$no = 1;

if (mysqli_num_rows($query) == 0) {
    $rows = '<tr><td colspan="6" style="text-align:center;">Belum ada data pengelola</td></tr>';
} else {
    while ($data = mysqli_fetch_assoc($query)) {

        $tanggal = !empty($data['created_at'])
            ? date('d-m-Y H:i:s', strtotime($data['created_at']))
            : 'N/A';

        $role_text = ($data['role'] == 'pengelola_lab')
            ? 'Pengelola Lab'
            : 'Pengelola Ruangan';

        $rows .= "
        <tr>
            <td style='text-align:center;'>$no</td>
            <td>" . htmlspecialchars($data['nama']) . "</td>
            <td>" . htmlspecialchars($data['email']) . "</td>
            <td>" . htmlspecialchars($data['username']) . "</td>
            <td>$role_text</td>
            <td>$tanggal</td>
        </tr>
        ";
        $no++;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Pengelola</title>

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
        h1 { color: #007bff; font-size: 24px; }
        .info-box { 
            background: #f8f9fa; 
            padding: 15px; 
            border-left: 4px solid #007bff;
            margin-bottom: 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th {
            background: #007bff;
            color: white;
            padding: 10px;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) { background: #f8f9fa; }
        table tr:hover { background: #e9ecef; }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <div class="header-logo">
            <img src="../../../assets/img/logo kotak.png" alt="Logo">
        </div>
        <div class="header-content">
            <h1>LAPORAN DATA PENGELOLA</h1>
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
            <th>Nama</th>
            <th>Email</th>
            <th>Username</th>
            <th>Role</th>
            <th width="180">Tanggal Ditambahkan</th>
        </tr>
        </thead>

        <tbody>
        <?= $rows; ?>
        </tbody>
    </table>

</div>

<script>
    document.getElementById('tanggal-cetak').textContent = 
        new Date().toLocaleString('id-ID');

    window.onload = () => window.print();
</script>

</body>
</html>
