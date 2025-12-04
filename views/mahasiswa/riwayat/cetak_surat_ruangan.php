<?php
session_start();

// Cek akses dan koneksi
if (!isset($_SESSION['id_mahasiswa']) || $_SESSION['role'] !== 'mahasiswa') {
    die("Akses ditolak. Silakan login.");
}

require '../../../settings/koneksi.php'; 
$db = new Database();
$koneksi = $db->conn;

$id_peminjaman = (int)($_GET['id'] ?? 0);

if ($id_peminjaman === 0) {
    die("ID Peminjaman tidak ditemukan.");
}

// =================================================================
// >>> 1. AMBIL DETAIL PEMINJAMAN <<<
// =================================================================
$query = "
    SELECT 
        p.id_peminjaman, 
        p.tanggal_pinjam, 
        p.jam_mulai, 
        p.jam_selesai, 
        p.keperluan,
        p.jumlah_peserta,
        p.status,
        r.nama_ruangan, 
        r.lokasi,
        m.nama AS nama_mahasiswa,
        m.nim,
        j.nama_prodi AS nama_jurusan 
    FROM peminjaman p
    INNER JOIN ruangan r ON p.ruangan_id = r.id_ruangan
    INNER JOIN mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa 
    INNER JOIN prodi j ON m.prodi_id = j.id_prodi 
    WHERE p.id_peminjaman = ? 
    AND p.mahasiswa_id = ? 
    AND p.status = 'disetujui'
    AND p.ruangan_id IS NOT NULL
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'ii', $id_peminjaman, $_SESSION['id_mahasiswa']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
$stmt->close(); 

if (!$data) {
    die("Data peminjaman tidak ditemukan atau belum disetujui. Pastikan statusnya 'Disetujui'.");
}

// Ambil nama Prodi mahasiswa (tetap dinamis untuk di badan surat)
$nama_prodi_mahasiswa = htmlspecialchars($data['nama_jurusan']); 

// =================================================================
// >>> 2. AMBIL NAMA PIMPINAN JURUSAN DARI TABEL USERS <<<
// =================================================================

$nama_pimpinan_jurusan = "NAMA PIMPINAN JURUSAN"; 
$nip_pimpinan_jurusan = "NIP/NIK BELUM TERCANTUM"; 

$sql_pimpinan = "SELECT nama, username FROM users WHERE role = 'jurusan' LIMIT 1"; 
$result_pimpinan = mysqli_query($koneksi, $sql_pimpinan);

if ($result_pimpinan && mysqli_num_rows($result_pimpinan) > 0) {
    $pimpinan_data = mysqli_fetch_assoc($result_pimpinan);
    $nama_pimpinan_jurusan = htmlspecialchars($pimpinan_data['nama']);
    // Menggunakan username sebagai NIP/NIK
    $nip_pimpinan_jurusan = htmlspecialchars($pimpinan_data['username']); 
}
// =================================================================


// Konversi tanggal ke format Indonesia
$tanggal_pinjam_indo = date('d F Y', strtotime($data['tanggal_pinjam']));
$tanggal_sekarang = date('d F Y');
$jam_mulai = date('H:i', strtotime($data['jam_mulai']));
$jam_selesai = date('H:i', strtotime($data['jam_selesai']));

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pemberitahuan Peminjaman Ruangan #<?= $data['id_peminjaman'] ?></title>
    <style>
        /* 1. KONTROL UKURAN A4 DAN MARGIN (PALING KETAT) */
        @page {
            size: A4;
            margin: 20mm 20mm 20mm 20mm; /* Dikurangi menjadi 2cm semua sisi */
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            margin: 0;
            padding: 0;
            line-height: 1.3; /* Paling rapat */
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
        }

        /* *** PENTING: OPTIMASI MARGIN PARAGRAF *** */
        p {
            margin: 0; 
        }
        .content p {
            margin-bottom: 5px; /* Dikurangi */
        }
        
        /* CSS HEADER KOP SURAT */
        .header {
            display: flex;
            align-items: center;
            padding-bottom: 10px;
            border-bottom: 3px solid #000;
            margin-bottom: 10px; /* Dikurangi */
        }
        .logo-box {
            flex-shrink: 0;
            width: 90px;
            margin-right: 15px;
            text-align: center;
        }
        .logo-box img {
            width: 85px; 
            height: auto;
        }
        .header-text {
            flex-grow: 1;
            text-align: center;
            line-height: 1.1; /* Paling rapat */
        }
        .header-text h3 {
            margin: 0;
            font-size: 1.3em;
            font-weight: bold;
        }
        .header-text h4 {
            margin: 0;
            font-size: 1.1em;
        }
        .header-text p {
            margin: 2px 0 0 0;
            font-size: 10pt;
        }
        
        /* Isi Konten dan Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0; /* Dikurangi */
        }
        .info-table td:first-child {
            width: 25%;
            padding: 2px 0;
            vertical-align: top;
        }
        .data-table {
            width: 80%;
            margin-left: auto;
            margin-right: auto;
            border: 1px solid #000;
        }
        .data-table td {
            padding: 6px 10px; /* Dikurangi */
            border: 1px solid #000;
        }
        .data-table tr td:first-child {
            font-weight: bold;
            width: 40%;
            background-color: #f0f0f0;
        }
        
        /* 3. OPTIMASI JARAK PADA TANDA TANGAN */
        .signature {
            margin-top: 20px; /* Dikurangi */
            text-align: right;
            font-size: 11pt;
        }
        .signature-name { 
            margin-top: 35px; 
            padding: 0 10px;
            display: inline-block;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        .signature p {
            margin: 0;
        }
        
        .note { 
            margin-top: 40px; 
            font-size: 10pt; 
            color: #555; 
            border: 1px solid #ccc; 
            padding: 10px; 
            background-color: #f9f9f9;
        }
        
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <div class="logo-box">
                <img src="../../../assets/img/Logo_Polije.png" alt="Logo Institusi"> 
            </div>
            <div class="header-text">
                <h3>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI<br>POLITEKNIK NEGERI JEMBER</h3>
                <h4>JURUSAN TEKNOLOGI INFORMASI</h4>
                <p>Jalan Mastrip, Kotak Pos 164, Jember 68101</p>
                <p>Telepon (0331) 333532, Fax (0331) 330218 | Laman: www.polije.ac.id</p>
            </div>
            <div class="logo-box" style="visibility: hidden;">
                <img src="../../../assets/img/Logo_Polije.png" alt="Logo Institusi"> 
            </div>
        </div>

        <div style="text-align: center; margin-bottom: 10px;"> 
            <p style="font-weight: bold; font-size: 1.1em; margin-bottom: 5px; text-decoration: underline;">SURAT PEMBERITAHUAN PEMINJAMAN RUANGAN</p>
            <p style="font-size: 10pt; color: #555; margin: 0;">Nomor: ______ /TI.4 / P.RUANGAN / <?= date('Y') ?></p>
            <p style="font-size: 10pt; color: #555; margin-top: 2px; margin-bottom: 0;">Kode Peminjaman: #<?= str_pad($data['id_peminjaman'], 5, '0', STR_PAD_LEFT) ?></p>
        </div>


        <div class="content">
            <p style="margin-top: 10px;">Kepada Yth.,</p>
            <p><strong>Bapak/Ibu Petugas Keamanan (Satpam)</strong><br>
            di [LOKASI KAMPUS/GEDUNG]<br>
            Di Tempat</p>

            <p style="text-indent: 30px;">Dengan hormat, Bersama surat ini, kami memberitahukan bahwa peminjaman ruangan yang diajukan oleh mahasiswa/i di bawah ini telah **disetujui** oleh pihak Jurusan/Manajemen:</p>
            
            <table class="info-table" style="width: 70%; margin-left: 30px;">
                <tr><td>Nama Peminjam</td><td>: <?= htmlspecialchars($data['nama_mahasiswa']) ?></td></tr>
                <tr><td>NIM / Jurusan</td><td>: <?= htmlspecialchars($data['nim']) ?> / <?= $nama_prodi_mahasiswa ?></td></tr>
                <tr><td>Keperluan</td><td>: <?= htmlspecialchars($data['keperluan']) ?></td></tr>
                <tr><td>Jumlah Peserta</td><td>: <?= htmlspecialchars($data['jumlah_peserta']) ?> Orang</td></tr>
            </table>

            <p style="text-indent: 30px; margin-top: 5px;">Adapun detail ruangan dan waktu peminjaman adalah sebagai berikut:</p>

            <table class="data-table">
                <tr><td>Nama Ruangan</td><td><strong><?= htmlspecialchars($data['nama_ruangan']) ?></strong></td></tr>
                <tr><td>Lokasi / Gedung</td><td><?= htmlspecialchars($data['lokasi']) ?></td></tr>
                <tr><td>Tanggal Pinjam</td><td><strong><?= $tanggal_pinjam_indo ?></strong></td></tr>
                <tr><td>Waktu Pinjam</td><td><strong><?= $jam_mulai ?> WIB - <?= $jam_selesai ?> WIB</strong></td></tr>
            </table>

            <p style="text-indent: 30px; margin-top: 5px;">Mohon kiranya Bapak/Ibu Petugas Keamanan dapat memberikan akses dan bantuan yang diperlukan sesuai dengan jadwal di atas.</p>
            
            <p style="text-indent: 30px;">Atas perhatian dan kerja samanya, kami ucapkan terima kasih.</p>
            
            <div class="signature">
                <p>[KOTA ANDA], <?= $tanggal_sekarang ?></p>
                <p>Mengetahui,</p>
                
                <p style="margin-top: 35px;">
                    <span class="signature-name"><?= $nama_pimpinan_jurusan ?></span>
                </p>
                <p>Pimpinan Jurusan Teknologi Informasi</p>
                <p style="margin-top: 5px; font-size: 10pt;">NIP/NIK: <?= $nip_pimpinan_jurusan ?></p>
            </div>

            <div class="note no-print">
                <strong>INSTRUKSI:</strong>
                <ol>
                    <li>Pastikan semua data sudah benar.</li>
                    <li>Gunakan menu cetak (*Print*) browser Anda (Ctrl+P / Cmd+P).</li>
                    <li>Pilih tujuan "Simpan sebagai PDF" atau printer Anda.</li>
                    <li>Surat ini harus diserahkan kepada Petugas Keamanan saat peminjaman.</li>
                </ol>
                <button onclick="window.print()" class="no-print btn btn-primary" style="padding: 10px; cursor: pointer; background-color: #007bff; color: white; border: none; border-radius: 5px; margin-top: 10px;">Cetak Surat Ini (PDF/Printer)</button>
            </div>
        </div>

    </div>

</body>
</html>