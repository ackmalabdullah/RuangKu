<?php
session_start();

// ... (logika pengecekan $required_role dan session) ...
// (Saya asumsikan logika pengecekan session Anda ada di sini)
// 4. Cek apakah role user SESUAI dengan role yang dibutuhkan halaman
// if ($_SESSION['role'] !== $required_role) {
//     // ... (kode redirect jika gagal) ...
// }

// 5. JIKA LOLOS SEMUA CEK, BARU VARIABEL DIBUAT
//    Variabel ini mengambil data dari SESSION (yang dibuat saat login)
// (Pastikan session 'nama' dan 'role' sudah di-set saat login)
$nama_user = htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); // '??' untuk nilai default
$role_user = htmlspecialchars($_SESSION['role'] ?? 'Tamu');
?>

<!DOCTYPE html>

<html
  lang="en"
  class="light-style layout-menu-fixed"
  dir="ltr"
  data-theme="theme-default"
  data-assets-path="../../../assets/assets_dashboard/assets/"
  data-template="vertical-menu-template-free"
>
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>APKRuangKu</title>

    <meta name="description" content="" />

    <link rel="icon" type="image/x-icon" href="../../../assets/assets_dashboard/assets/img/favicon/favicon.ico" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
      rel="stylesheet"
    />

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