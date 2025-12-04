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

$query = mysqli_query($conn, "SELECT id_fasilitas, nama_fasilitas, created_at 
                                 FROM fasilitas 
                                 ORDER BY nama_fasilitas ASC");

if (!$query) {
    die('ERROR Query: ' . mysqli_error($conn));
}

if (mysqli_num_rows($query) == 0) {
    $rows = '<tr><td colspan="3" style="text-align: center;">Belum ada data fasilitas</td></tr>';
} else {
    $rows = '';
    $no = 1;
    while ($data = mysqli_fetch_assoc($query)) {
        $tanggal_ditambah = !empty($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : 'N/A';
        $rows .= '
        <tr>
            <td style="text-align: center;">' . $no++ . '</td>
            <td>' . htmlspecialchars($data['nama_fasilitas']) . '</td>
            <td>' . $tanggal_ditambah . '</td>
        </tr>
        ';
    }
}

$user_nama = $_SESSION['nama_user'] ?? 'Admin';
$user_email = $_SESSION['email'] ?? '';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cetak PDF - Laporan Fasilitas</title>
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
            flex-shrink: 0;
            margin-right: 20px;
        }
        .header-logo img {
            width: 100%;
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
        }
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        table tr:hover {
            background-color: #e9ecef;
        }
        .footer { 
            text-align: center; 
            color: #999; 
            font-size: 11px; 
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        @media print { 
            body { margin: 0; padding: 0; }
            .container { margin: 0; padding: 0; }
            table { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-logo">
                <img src="../../../assets//img/logo kotak.png" alt="RuangKu Logo" style="width: 100%; height: auto;">
            </div>
            <div class="header-content">
                <h1>LAPORAN DATA SEMUA FASILITAS LAB</h1>
                <p>RuangKu Management System</p>
            </div>
        </div>
        
        <div class="info-box">
            <p><strong>User Login:</strong> <?php echo htmlspecialchars($user_nama); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user_email); ?></p>
            <!-- Tanggal cetak now updates real-time with JavaScript -->
            <p><strong>Tanggal Cetak:</strong> <span id="tanggal-cetak"></span></p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Nama Fasilitas</th>
                    <th width="200">Tanggal Ditambahkan</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $rows; ?>
            </tbody>
        </table>
        
        <!-- Footer removed - no more copyright or page number -->
    </div>

    <script>
        function updateTanggalCetak() {
            const now = new Date();
            const day = String(now.getDate()).padStart(2, '0');
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const year = now.getFullYear();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            
            const tanggalFormatted = `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
            document.getElementById('tanggal-cetak').textContent = tanggalFormatted;
        }

        // Update immediately on page load
        updateTanggalCetak();
        
        // Update every second
        setInterval(updateTanggalCetak, 1000);

        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
