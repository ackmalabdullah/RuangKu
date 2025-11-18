<?php
// ---------------------------------------------------------
// DATA ASLI DARI DATABASE (TINGGAL SESUAIKAN QUERY-MU)
// ---------------------------------------------------------
$nama_user  = $dataUser['nama'] ?? "";
$nim_user   = $dataUser['nim'] ?? "";
$peminjaman_aktif = $jumlahPeminjamanAktif ?? 0;
$buku_jatuh_tempo = $jumlahJatuhTempo ?? 0;
$sks_terdaftar    = $jumlahSKS ?? 0;
$riwayat_peminjaman_terakhir = $riwayatTerakhir ?? []; 
// ---------------------------------------------------------

$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header Selamat Datang -->
    <div class="row">
        <div class="col-lg-8 mb-4 order-0">
            <div class="card shadow-sm">
                <div class="d-flex align-items-end row">
                    <div class="col-sm-7">
                        <div class="card-body">
                            <h2 class="card-title text-primary">
                                Halo, <?= htmlspecialchars($nama_user); ?>! 👋
                            </h2>

                            <p class="mb-4">
                                NIM Anda:
                                <span class="fw-bold">
                                    <?= htmlspecialchars($nim_user); ?>
                                </span>.
                                Periksa riwayat peminjaman Anda dan pastikan tidak ada buku yang terlambat.
                            </p>

                            <a href="peminjaman.php" class="btn btn-primary btn-sm">
                                Lihat Peminjaman Aktif
                            </a>
                            <a href="profile.php" class="btn btn-outline-primary btn-sm ms-2">
                                Atur Profil
                            </a>
                        </div>
                    </div>

                    <div class="col-sm-5 text-center text-sm-left">
                        <div class="card-body pb-0 px-0 px-md-4">
                            <img
                                src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png"
                                height="140"
                                alt="Student Illustration"
                                data-app-dark-img="illustrations/man-with-laptop-dark.png"
                                data-app-light-img="illustrations/man-with-laptop-light.png"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik -->
        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">

                <!-- Peminjaman Aktif -->
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-book"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Peminjaman Aktif</span>
                            <h3 class="card-title mb-2"><?= $peminjaman_aktif; ?> Buku</h3>
                            <small class="text-muted">
                                Limit 5 Buku
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Jatuh Tempo -->
                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="bx bx-time-five"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="d-block mb-1">Jatuh Tempo</span>
                            <h3 class="card-title text-nowrap mb-2"><?= $buku_jatuh_tempo; ?> Buku</h3>
                            <small class="text-danger fw-semibold">
                                Segera Kembalikan
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Row 2 -->
    <div class="row">

        <!-- Chart Statistik -->
        <div class="col-12 col-lg-8 mb-4">
            <div class="card shadow-sm">
                <h5 class="card-header">Statistik Riwayat Peminjaman (6 Bulan Terakhir)</h5>
                <div class="p-4 text-center" style="min-height:200px;">
                    <i class="bx bx-bar-chart-alt text-xl me-2"></i>
                    Grafik tren peminjaman akan muncul di sini.
                </div>
            </div>
        </div>

        <!-- Informasi Akademik -->
        <div class="col-12 col-md-8 col-lg-4">
            <div class="row">

                <div class="col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-info">
                                        <i class="bx bx-chalkboard"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="d-block mb-1">SKS Terdaftar</span>
                            <h3 class="card-title"><?= $sks_terdaftar; ?></h3>
                            <small class="text-success">Semester Ganjil</small>
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-key"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="d-block mb-1">Keamanan Akun</span>
                            <a href="ganti_password.php" class="btn btn-warning btn-sm mt-2 w-100">
                                Ganti Password
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>


    <!-- Row 3 -->
    <div class="row">

        <!-- Riwayat 5 Terakhir -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="m-0">5 Riwayat Peminjaman Terakhir</h5>
                    <small class="text-muted">Status buku yang dipinjam</small>
                </div>

                <div class="card-body">
                    <?php if (!empty($riwayat_peminjaman_terakhir)): ?>
                    <ul class="p-0 m-0">
                        <?php foreach ($riwayat_peminjaman_terakhir as $item): ?>
                        <li class="d-flex mb-4 pb-1">
                            <div class="avatar flex-shrink-0 me-3">
                                <span class="avatar-initial rounded bg-label-<?= $item['status']=='Pinjam'?'primary':'secondary'; ?>">
                                    <i class="bx bx-book-bookmark"></i>
                                </span>
                            </div>

                            <div class="d-flex w-100 justify-content-between">
                                <div>
                                    <h6 class="mb-0"><?= htmlspecialchars($item['judul']); ?></h6>
                                    <small class="text-muted">Tanggal Pinjam: <?= $item['tanggal_pinjam']; ?></small>
                                </div>

                                <span class="badge bg-label-<?= $item['status']=='Pinjam'?'success':'secondary'; ?>">
                                    <?= $item['status']; ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <p class="text-muted text-center py-3">Tidak ada riwayat peminjaman.</p>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <a href="riwayat_peminjaman.php" class="btn btn-outline-secondary btn-sm">
                            Lihat Semua Riwayat
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- Kontak Penting -->
        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="m-0">Kontak Penting</h5>
                </div>
                <div class="card-body">

                    <?php if (!empty($kontak_penting)): ?>
                        <ul class="p-0 m-0">
                            <?php foreach ($kontak_penting as $kontak): ?>
                            <li class="d-flex mb-4 pb-1">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-primary">
                                        <i class="bx bx-user"></i>
                                    </span>
                                </div>

                                <div class="d-flex w-100 justify-content-between">
                                    <div>
                                        <small class="text-muted d-block"><?= $kontak['jabatan']; ?></small>
                                        <h6 class="mb-0"><?= $kontak['nama']; ?></h6>
                                    </div>
                                    <span class="badge bg-label-primary">Hubungi</span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">Belum ada kontak penting tersedia.</p>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>

