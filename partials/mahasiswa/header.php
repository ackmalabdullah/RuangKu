<?php
// 1. Mulai Session
session_start();

// 2. Hubungkan ke Database
// Path ini (relative terhadap header.php) harus benar
require_once __DIR__ . '/../../settings/koneksi.php';

// Inisialisasi koneksi jika belum ada
if (!isset($koneksi) || !$koneksi) {
    $database = new Database();
    $koneksi = $database->conn;
}

// 3. Cek Sesi Login (Perbaikan Utama di sini)
// Gunakan variabel ROLE yang harus diset oleh SEMUA user saat login
if (!isset($_SESSION['role']) || $_SESSION['role'] === 'tamu') {
    // Jika 'role' tidak ada, anggap belum login
    header("Location: ../../auth/login.php?pesan=belum_login");
    exit;
}
// JANGAN MENGGUNAKAN $_SESSION['id_mahasiswa'] karena hanya ada untuk Mahasiswa.

// 4. Cek Role (Sudah benar: membandingkan role sesi dengan $required_role dari file pemanggil)
if (isset($required_role)) {
    // Ambil role dari session
    $role_login = $_SESSION['role']; // Kita yakin ini sudah ada karena lolos pengecekan di atas

    if ($role_login !== $required_role) {
        // Jika role tidak sesuai, lempar ke halaman yang sesuai
        echo "Akses ditolak. Role Anda ($role_login) tidak diizinkan.";
        // Atau: Arahkan ke dashboard sesuai role (opsional, tergantung struktur folder Anda)
        // header("Location: ../../" . $role_login . "/dashboard.php"); 
        exit;
    }
}


// 5. Ambil Data User Lengkap dari Database
// **PERUBAHAN:** Hanya ambil data dari tabel 'mahasiswa' JIKA role-nya adalah mahasiswa.
if (isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa') {

    // Kita gunakan ID dari session untuk mengambil data terbaru
    $id_user_login = $_SESSION['id_mahasiswa']; // Hanya Mahasiswa yang punya ini
    
    // Pastikan ID ada sebelum query
    if (!isset($id_user_login)) {
         session_destroy();
         header("Location: ../../auth/login.php?pesan=sesi_tidak_valid");
         exit;
    }
    
    $stmt_header = $koneksi->prepare("SELECT nama, foto FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt_header->bind_param("i", $id_user_login);
    $stmt_header->execute();
    $result_header = $stmt_header->get_result();
    $stmt_header->close(); // Tutup statement

    // Inisialisasi default data mahasiswa
    $user_data = ['nama' => 'Mahasiswa', 'foto' => 'default.png'];

    if ($result_header->num_rows > 0) {
        $user_data = $result_header->fetch_assoc();
    } else {
        // Jika data tidak ditemukan, hancurkan session
        session_destroy();
        header("Location: ../../auth/login.php?pesan=sesi_tidak_valid");
        exit;
    }

    // Simpan data ke variabel yang akan dipakai di navbar & halaman
    $nama_user = htmlspecialchars($user_data['nama']);
    $role_user = 'Mahasiswa'; 
    $foto_data_db = $user_data['foto']; // Nama file foto dari DB

} else {
    // JIKA BUKAN MAHASISWA (Admin, Pengelola, dll.)
    
    // Ambil nama dan role dari session (Harus diset di proses login masing-masing)
    $nama_user = htmlspecialchars($_SESSION['nama'] ?? $_SESSION['role']); // Ambil nama dari sesi (Asumsi: proses login set $_SESSION['nama'])
    $role_user = htmlspecialchars($_SESSION['role']);
    $foto_data_db = 'default.png'; // Gunakan default untuk role lain

}

// --- Logika Penentu Foto Profil UNIVERSAL ---

// Path untuk <img> di HTML (relatif ke file utama seperti profile.php)
$path_html_foto = "../../../assets/img/avatars/";

// Path untuk 'file_exists' di PHP (relatif ke file header.php ini)
// **PERBAIKAN PATH:** Naik 2 level (ke partials) lalu ke assets
$path_php_check = __DIR__ . '/../../assets/img/avatars/';

$foto_profil_tampil = "default.png"; // Foto default

// Cek apakah user punya foto, tidak kosong, DAN filenya ada
if (isset($foto_data_db) && !empty($foto_data_db) && file_exists($path_php_check . $foto_data_db)) {
    $foto_profil_tampil = $foto_data_db;
}

// Ini adalah variabel final untuk <img>
$src_foto_profil = $path_html_foto . htmlspecialchars($foto_profil_tampil);

// -- SEKARANG, SEMUA VARIABEL ($nama_user, $role_user, $src_foto_profil) SIAP --
// -- Dan HTML di bawah ini bisa langsung memakainya --

?>
<!DOCTYPE html>

<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../assets/assets_dashboard/assets/"
  data-template="vertical-menu-template-free">

<head>
  <meta charset="utf-8" />
  <meta
    name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>APKRuangKu</title>

  <meta name="description" content="" />

  <link rel="icon" type="image/x-icon" href="../../../assets/assets_dashboard/assets/img/favicon/favicon.ico" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
    rel="stylesheet" />

  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/vendor/fonts/boxicons.css" />

  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/vendor/css/core.css" class="template-customizer-core-css" />
  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/vendor/css/theme-default.css" class="template-customizer-theme-css" />
  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/css/demo.css" />

  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

  <link rel="stylesheet" href="../../../assets/assets_dashboard/assets/vendor/libs/apex-charts/apex-charts.css" />

  <script src="../../../assets/assets_dashboard/assets/vendor/js/helpers.js"></script>

  <script src="../../../assets/assets_dashboard/assets/js/config.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      ```