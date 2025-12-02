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

// AMBIL INFO USER LOGIN ---
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

// --- AMBIL DATA MAHASISWA ---
$sql = "
    SELECT m.*, p.nama_prodi
    FROM mahasiswa m
    LEFT JOIN prodi p ON m.prodi_id = p.id_prodi
    ORDER BY m.nama ASC
";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die('ERROR Query: ' . mysqli_error($conn));
}

$rows = "";
$no = 1;

if (mysqli_num_rows($query) == 0) {
    $rows = '<tr><td colspan="8" style="text-align:center;">Belum ada data mahasiswa</td></tr>';
} else {
    while ($m = mysqli_fetch_assoc($query)) {

        $foto_filename = $m['foto'];

        // path untuk file_exists (path sistem)
        $file_path = __DIR__ . "/../../../assets/img/avatars/" . $foto_filename;

        // path untuk img src (path URL)
        $url_path = "/RuangKu/assets/img/avatars/" . $foto_filename;

        // fallback
        $default_url = "/RuangKu/assets/img/avatars/default.png";

        if (!empty($foto_filename) && file_exists($file_path)) {
            $foto_html = "
                <img src='$url_path'
                    style='width:75px; height:100px; object-fit:cover; border-radius:6px;'>
            ";
        } else {
            $foto_html = "
                <img src='$default_url'
                    style='width:75px; height:100px; object-fit:cover; border-radius:6px;'>
            ";
        }


        $rows .= "
        <tr>
            <td style='text-align:center;'>$no</td>
            <td>".htmlspecialchars($m['nim'])."</td>
            <td>".htmlspecialchars($m['nama'])."</td>   
            <td>".htmlspecialchars($m['email'])."</td>
            <td>".htmlspecialchars($m['nama_prodi'])."</td>
            <td>".htmlspecialchars($m['angkatan'])."</td>
            <td style='text-align:center;'>$foto_html</td>
        </tr>";
        $no++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Data Mahasiswa</title>

    <style>
        * { margin: 0; padding: 0; }
        body { 
            font-family: 'Montserrat', sans-serif; 
            line-height: 1.6;
            color: #333;
        }
        .container { 
            max-width: 1100px; 
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
        h1 { color: #007bff; margin-bottom: 5px; }

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
            text-align: left;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) { background: #f8f9fa; }
        table tr:hover { background: #eef3f8; }
    </style>
</head>

<body>
<div class="container">

    <div class="header">
        <div class="header-logo">
            <img src="../../../assets/img/logo kotak.png" alt="Logo">
        </div>
        <div class="header-content">
            <h1>LAPORAN DATA MAHASISWA</h1>
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
            <th width="40">No</th>
            <th width="110">NIM</th>
            <th>Nama</th>
            <th>Email</th>
            <th width="180">Program Studi</th>
            <th width="90">Angkatan</th>
            <th width="80">Foto</th>
        </tr>
        </thead>
        <tbody>
            <?= $rows ?>
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
