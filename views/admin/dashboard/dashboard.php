<?php
$required_role = 'admin';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Koneksi database
require_once '../../../settings/koneksi.php';
$db   = new Database();
$conn = $db->conn;

if (!$conn) {
    die("Koneksi database gagal!");
}

// AMBIL DATA MASTER – SUDAH DIPERBAIKI TOTAL
$total_prodi       = $conn->query("SELECT COUNT(*) FROM prodi")->fetch_row()[0] ?? 0;
$total_kategori    = $conn->query("SELECT COUNT(*) FROM kategori_ruangan")->fetch_row()[0] ?? 0;
$total_mahasiswa   = $conn->query("SELECT COUNT(*) FROM mahasiswa")->fetch_row()[0] ?? 0;

// PENGELOLA: gabungin pengelola_ruangan + pengelola_lab
$query_pengelola = "SELECT COUNT(*) FROM users 
                    WHERE role = 'pengelola_ruangan' 
                       OR role = 'pengelola_lab'";
$total_pengelola   = $conn->query($query_pengelola)->fetch_row()[0] ?? 0;
?>

<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Selamat Datang -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body d-flex align-items-center justify-content-between flex-wrap gap-4">
                    <div>
                        <h2 class="text-white mb-1">Selamat Datang, <?= htmlspecialchars($nama_user) ?>!</h2>
                        <p class="mb-0 opacity-90 fs-5">Kelola data master sistem peminjaman ruangan.</p>
                    </div>
                    <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png" 
                         height="140" 
                         class="d-none d-lg-block" 
                         alt="Admin Illustration"
                         data-app-dark-img="../../../assets/img/illustrations/man-with-laptop-dark.png"
                         data-app-light-img="../../../assets/img/illustrations/man-with-laptop-light.png">
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Stat Card Utama -->
    <div class="row g-4 mb-5">
        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="avatar bg-label-info rounded mb-3 mx-auto">
                        <i class="bx bx-building bx-lg"></i>
                    </div>
                    <span class="d-block fw-medium">Program Studi</span>
                    <h3 class="mb-0"><?= number_format($total_prodi) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="avatar bg-label-warning rounded mb-3 mx-auto">
                        <i class="bx bx-category bx-lg"></i>
                    </div>
                    <span class="d-block fw-medium">Kategori Ruangan</span>
                    <h3 class="mb-0"><?= number_format($total_kategori) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="avatar bg-label-success rounded mb-3 mx-auto">
                        <i class="bx bx-group bx-lg"></i>
                    </div>
                    <span class="d-block fw-medium">Mahasiswa</span>
                    <h3 class="mb-0"><?= number_format($total_mahasiswa) ?></h3>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="card h-100 text-center border-0 shadow-sm">
                <div class="card-body">
                    <div class="avatar bg-label-primary rounded mb-3 mx-auto">
                        <i class="bx bx-user-check bx-lg"></i>
                    </div>
                    <span class="d-block fw-medium">Pengelola</span>
                    <h3 class="mb-0"><?= number_format($total_pengelola) ?></h3>
                    <small class="text-muted d-block">Ruangan + Lab</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Menu Cepat Admin -->
    <div class="card">
        <div class="card-body py-5">
            <div class="row row-cols-2 row-cols-md-4 g-4 text-center">
                <div class="col">
                    <a href="../prodi/prodi.php" class="text-decoration-none text-dark">
                        <div class="btn btn-outline-info rounded-circle p-4 mb-2">
                            <i class="bx bx-building bx-lg"></i>
                        </div>
                        <p class="mb-0 fw-semibold">Program Studi</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../kategori/kategori.php" class="text-decoration-none text-dark">
                        <div class="btn btn-outline-warning rounded-circle p-4 mb-2">
                            <i class="bx bx-category bx-lg"></i>
                        </div>
                        <p class="mb-0 fw-semibold">Kategori</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../mahasiswa/mahasiswa.php" class="text-decoration-none text-dark">
                        <div class="btn btn-outline-success rounded-circle p-4 mb-2">
                            <i class="bx bx-group bx-lg"></i>
                        </div>
                        <p class="mb-0 fw-semibold">Mahasiswa</p>
                    </a>
                </div>
                <div class="col">
                    <a href="../pengelola/pengelola.php" class="text-decoration-none text-dark">
                        <div class="btn btn-outline-dark rounded-circle p-4 mb-2">
                            <i class="bx bx-user-check bx-lg"></i>
                        </div>
                        <p class="mb-0 fw-semibold">Pengelola</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>