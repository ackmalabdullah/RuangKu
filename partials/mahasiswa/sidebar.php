<?php
// session_start() seharusnya sudah dipanggil di file header.php
// Kita ambil role-nya dari session. 
// Default ke 'mahasiswa' jika session tidak ada (untuk keamanan)
$role = $_SESSION['role'] ?? 'mahasiswa';

// FUNGSI BANTUAN: Untuk menandai menu yang sedang aktif
// Ini akan mengambil nama file PHP yang sedang dibuka
// Contoh: /views/admin/dashboard.php -> akan diambil 'dashboard.php'
function isActive($menuPageName)
{
  $currentPage = basename($_SERVER['SCRIPT_NAME']);
  if ($currentPage == $menuPageName) {
    return 'active';
  }
  return '';
}
?>

<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <div class="app-brand demo">

    <a href="#" class="app-brand-link">

      <span class="app-brand-logo demo">
        <img src="../../../assets/img/logo kotak.png" alt="Logo" width="40" style="border-radius: 6px;">
      </span>


      <span class="app-brand-text demo menu-text fw-bolder ms-2">RuangKu</span>

    </a>



    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">

      <i class="bx bx-chevron-left bx-sm align-middle"></i>

    </a>

  </div>



  <div class="menu-inner-shadow"></div>



  <ul class="menu-inner py-1">

    <ul class="menu-inner py-1">
      <?php if ($role == 'mahasiswa') : ?>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Mahasiswa</span>
        </li>

        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
          <a href="../dashboard/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div>Dashboard</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('peminjaman.php'); ?>">
          <a href="../peminjaman/peminjaman.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-calendar-plus"></i>
            <div>Peminjaman</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('profile.php'); ?>">
          <a href="../profile/profile.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user"></i>
            <div>Profile</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('ganti_password.php'); ?>">
          <a href="../profile/ganti_password.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-lock-alt"></i>
            <div>Ganti Password</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('riwayat.php'); ?>">
          <a href="../riwayat/riwayat.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-history"></i>
            <div>Riwayat (Peminjaman)</div>
          </a>
        </li>

        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Logout</span>
        </li>

        <li class="menu-item">
          <a href="../../auth/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>

      <?php endif; ?>


      <?php if ($role == 'admin') : ?>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Administrator</span>
        </li>

        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
          <a href="../dashboard/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bxs-dashboard"></i>
            <div>Dashboard (Admin)</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('pengelola.php'); ?>">
          <a href="../pengelola/pengelola.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-pin"></i>
            <div>CRUD Pengelola</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('mahasiswa.php'); ?>">
          <a href="../mahasiswa/mahasiswa.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
            <div>CRUD Mahasiswa</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('prodi.php'); ?>">
          <a href="../prodi/prodi.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
            <div>CRUD Prodi</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('kategori.php'); ?>">
          <a href="../kategori/kategori.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-category"></i>
            <div>CRUD Kategori</div>
          </a>
        </li>

         <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Logout</span>
        </li>

        <li class="menu-item">
          <a href="../../auth/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>
      <?php endif; ?>


      <?php if ($role == 'jurusan') : ?>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Administrator</span>
        </li>

        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
          <a href="../dashboard/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bxs-dashboard"></i>
            <div>Dashboard (Admin)</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('lab.php'); ?>">
          <a href="../lab/lab.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-pin"></i>
            <div>Laboratorium</div>
          </a>
        </li>

        <li class="menu-item <?php echo isActive('persetujuan.php'); ?>">
          <a href="../persetujuan/persetujuan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-user-pin"></i>
            <div>Persetujuan</div>
          </a>
        </li>

        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Logout</span>
        </li>

        <li class="menu-item">
          <a href="../../auth/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>
      <?php endif; ?>


      <?php if ($role == 'pengelola_lab') : ?>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Kepala Lab</span>
        </li>
        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
          <a href="../dashboard/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bxs-dashboard"></i>
            <div>Dashboard (Kepala Lab)</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('fasilitas_lab.php'); ?>">
          <a href="../fasilitas_lab/fasilitas_lab.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-building-house"></i>
            <div>Fasilitas Lab</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('persetujuan.php'); ?>">
          <a href="../persetujuan/persetujuan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-check-shield"></i>
            <div>Persetujuan</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('riwayat.php'); ?>">
          <a href="../riwayat/riwayat.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-content"></i>
            <div>Riwayat Peminjaman Lab</div>
          </a>
        </li>
        <li class="menu-item has-sub <?= (isActive('laporan_peminjaman.php') || isActive('laporan_lab.php')) ? 'open' : '' ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-file-blank"></i>
            <div>Laporan</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item <?= isActive('laporan_peminjaman.php') ?>">
              <a href="../laporan/laporan_peminjaman.php" class="menu-link" onclick="event.stopPropagation()">
                <div data-i18n="Peminjaman">Peminjaman</div>
              </a>
            </li>
            <li class="menu-item <?= isActive('laporan_lab.php') ?>">
              <a href="../laporan/laporan_lab.php" class="menu-link" onclick="event.stopPropagation()">
                <div data-i18n="Lab">Lab</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Logout</span>
        </li>
        <li class="menu-item">
          <a href="../../auth/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>
      <?php endif; ?>

      <?php if ($role == 'pengelola_ruangan') : ?>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Pengelola</span>
        </li>
        <li class="menu-item <?php echo isActive('dashboard.php'); ?>">
          <a href="../dashboard/dashboard.php" class="menu-link">
            <i class="menu-icon tf-icons bxs-dashboard"></i>
            <div>Dashboard (Pengelola)</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('fasilitas_ruangan.php'); ?>">
          <a href="../fasilitas_ruangan/fasilitas_ruangan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-building-house"></i>
            <div>Fasilitas Ruangan</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('ruangan.php'); ?>">
          <a href="../ruangan/ruangan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-building-house"></i>
            <div>Ruangan</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('persetujuan.php'); ?>">
          <a href="../persetujuan/persetujuan.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-check-shield"></i>
            <div>Persetujuan</div>
          </a>
        </li>
        <li class="menu-item <?php echo isActive('riwayat.php'); ?>">
          <a href="../riwayat/riwayat.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-book-content"></i>
            <div>Riwayat Peminjaman Ruangan</div>
          </a>
        </li>
        <li class="menu-item has-sub <?= (isActive('laporan_peminjaman.php') || isActive('laporan_ruangan.php')) ? 'open' : '' ?>">
          <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-file-blank"></i>
            <div>Laporan</div>
          </a>
          <ul class="menu-sub">
            <li class="menu-item <?= isActive('laporan_peminjaman.php') ?>">
              <a href="../laporan/laporan_peminjaman.php" class="menu-link" onclick="event.stopPropagation()">
                <div data-i18n="Peminjaman">Peminjaman</div>
              </a>
            </li>
            <li class="menu-item <?= isActive('laporan_ruangan.php') ?>">
              <a href="../laporan/laporan_ruangan.php" class="menu-link" onclick="event.stopPropagation()">
                <div data-i18n="Ruangan">Ruangan</div>
              </a>
            </li>
          </ul>
        </li>
        <li class="menu-header small text-uppercase">
          <span class="menu-header-text">Logout</span>
        </li>
        <li class="menu-item">
          <a href="../../auth/logout.php" class="menu-link">
            <i class="menu-icon tf-icons bx bx-log-out"></i>
            <div>Logout</div>
          </a>
        </li>

      <?php endif; ?>
    </ul>

</aside>

<div class="layout-page">