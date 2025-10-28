<?php
$base_path = "/APKPINRULAB";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?php echo $judul_halaman; ?> - APKPINRULAB</title>

  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/header_mahasiswa.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>/assets/css/footer_dalam.css">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

  <header class="navbar">

    <a href="/APKPINRULAB/mahasiswa/dashboard" class="logo">
      APKPINRULAB
    </a>

    <nav>
      <ul>
        <li>
          <a class="<?php if ($halaman_aktif == 'dashboard') echo 'active'; ?>"
            href="/APKPINRULAB/mahasiswa/dashboard">
            Dashboard
          </a>
        </li>
        <li>
          <a class="<?php if ($halaman_aktif == 'peminjaman') echo 'active'; ?>"
            href="/APKPINRULAB/mahasiswa/peminjaman">
            Peminjaman
          </a>
        </li>
        <li>
          <a class="<?php if ($halaman_aktif == 'profile') echo 'active'; ?>"
            href="/APKPINRULAB/mahasiswa/profile">
            Profile
          </a>
        </li>
        <li>
          <a class="<?php if ($halaman_aktif == 'riwayat') echo 'active'; ?>"
            href="/APKPINRULAB/mahasiswa/riwayat">
            Riwayat
          </a>
        </li>
      </ul>
    </nav>

  </header>
  <main class="container">