<?php
// ---------------------------------------------------------
// DATA AWAL DARI DATABASE (Variabel Awal Anda)
// Catatan: Variabel-variabel ini akan di-overwrite oleh hasil query DB di bawah.
// ---------------------------------------------------------
$nama_user = $dataUser['nama'] ?? "";
$nim_user = $dataUser['nim'] ?? "";
$sks_terdaftar = $jumlahSKS ?? 0;
// ---------------------------------------------------------

$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php'; // ASUMSI: Koneksi DB ($pdo) sudah tersedia di sini
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';


// =========================================================
//  QUERY DATABASE UNTUK PEMINJAMAN RUANGAN (db_pinrulab)
// =========================================================

// Ambil ID Mahasiswa yang sedang login
$id_mahasiswa_login = $dataUser['id'] ?? 0;

// Inisialisasi variabel untuk menghindari error jika query gagal
$peminjaman_aktif = 0;
$peminjaman_ditolak = 0;
$peminjaman_menunggu = 0;
$riwayat_peminjaman_terakhir = [];
$statistik_peminjaman_bulan = [];
$ruangan_favorit = [];
$sks_terdaftar = $jumlahSKS ?? 0;

if (isset($pdo) && $pdo instanceof PDO && $id_mahasiswa_login > 0) {

    $stmtAktif = $pdo->prepare("
    SELECT COUNT(*) 
    FROM peminjaman 
    WHERE mahasiswa_id = ? 
      AND status = 'disetujui' 
      AND tanggal_pinjam >= CURDATE()
");
    $stmtAktif->execute([$id_mahasiswa_login]);
    $peminjaman_aktif = $stmtAktif->fetchColumn();


    $stmtDitolak = $pdo->prepare("
    SELECT COUNT(*) 
    FROM peminjaman 
    WHERE mahasiswa_id = ? 
      AND (status = 'ditolak' OR status = 'dibatalkan')
");
    $stmtDitolak->execute([$id_mahasiswa_login]);
    $peminjaman_ditolak = $stmtDitolak->fetchColumn();


    $stmtMenunggu = $pdo->prepare("
    SELECT COUNT(*) 
    FROM peminjaman 
    WHERE mahasiswa_id = ? 
      AND status = 'menunggu'
");
    $stmtMenunggu->execute([$id_mahasiswa_login]);
    $peminjaman_menunggu = $stmtMenunggu->fetchColumn();


    $sqlRiwayat = "
    SELECT 
        p.id_peminjaman,
        p.tanggal_pinjam, 
        p.status,
        p.jam_mulai,
        p.jam_selesai,
        p.keperluan,
        r.nama_ruangan, 
        a.nama AS approved_by_nama 
    FROM peminjaman p
    LEFT JOIN ruangan r ON p.ruangan_id = r.id_ruangan
    LEFT JOIN user a ON p.approved_by = a.id
    WHERE p.mahasiswa_id = ?
    ORDER BY p.id_peminjaman DESC
    LIMIT 5
";



    // 5. Ruangan Favorit (Data untuk Bar Chart/List) - Top 3
    $sqlFavorit = "
    SELECT 
        r.nama_ruangan AS nama, 
        COUNT(p.id_peminjaman) AS frekuensi
    FROM peminjaman p
    JOIN ruangan r ON p.ruangan_id = r.id_ruangan
    WHERE p.mahasiswa_id = ?
    GROUP BY r.nama_ruangan
    ORDER BY frekuensi DESC
    LIMIT 3
";



    // 6. Statistik Tren Bulanan (Data untuk Grafik Garis/Line Chart) - 6 Bulan Terakhir
    // CATATAN: QUERY NYATA HARUS MENGGUNAKAN GROUP BY MONTH DAN YEAR
    $statistik_peminjaman_bulan = [
        // Data ini adalah simulasi, ganti dengan hasil query agregasi bulanan
        ['bulan' => 'Jun', 'total' => 5],
        ['bulan' => 'Jul', 'total' => 8],
        ['bulan' => 'Agu', 'total' => 4],
        ['bulan' => 'Sep', 'total' => 10],
        ['bulan' => 'Okt', 'total' => 7],
        ['bulan' => 'Nov', 'total' => 12],
    ];
}

// Data Kontak Penting (Statis/Dummy)
$kontak_penting = [
    ['nama' => 'Bapak Budi', 'jabatan' => 'Admin Ruangan Lab', 'kontak' => '081234567890'],
    ['nama' => 'Ibu Wati', 'jabatan' => 'Staff Akademik', 'kontak' => '085098765432'],
];
?>

<div class="container-xxl flex-grow-1 container-p-y">

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
                                Periksa riwayat peminjaman Ruangan/Lab Anda.
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
                                data-app-light-img="illustrations/man-with-laptop-light.png" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 order-1">
            <div class="row">

                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-success">
                                        <i class="bx bx-check-shield"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="fw-semibold d-block mb-1">Disetujui (Aktif)</span>
                            <h3 class="card-title mb-2"><?= $peminjaman_aktif; ?> Ruangan</h3>
                            <small class="text-muted">
                                Siap digunakan
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-12 col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-warning">
                                        <i class="bx bx-hourglass"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="d-block mb-1">Menunggu</span>
                            <h3 class="card-title text-nowrap mb-2"><?= $peminjaman_menunggu; ?> Ruangan</h3>
                            <small class="text-warning fw-semibold">
                                Permintaan
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-md-12 col-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="avatar flex-shrink-0">
                                    <span class="avatar-initial rounded bg-label-danger">
                                        <i class="bx bx-x-circle"></i>
                                    </span>
                                </div>
                            </div>
                            <span class="d-block mb-1">Permintaan Ditolak</span>
                            <h5 class="card-title text-nowrap mb-2"><?= $peminjaman_ditolak; ?></h5>
                            <small class="text-danger fw-semibold">
                                Ditolak / Dibatalkan
                            </small>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="row">

        <div class="col-12 col-lg-8 mb-4">
            <div class="card shadow-sm h-100">
                <h5 class="card-header">Statistik Peminjaman Ruangan (6 Bulan Terakhir)</h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-7 border-end p-4">
                            <h6>Tren Jumlah Peminjaman Bulanan</h6>

                            <div class="p-4 text-center border rounded">
                                <i class="bx bx-line-chart-down text-xl me-2 text-primary"></i>
                                **GRAFIK GARIS (Line Chart)** akan dimuat di sini.
                                <br>
                                Data PHP untuk Grafik:
                                <pre style="font-size: 0.75rem;">
                                    <?php
                                    // Data ini siap digunakan oleh library Chart.js, misalnya:
                                    $labels = array_column($statistik_peminjaman_bulan, 'bulan');
                                    $data = array_column($statistik_peminjaman_bulan, 'total');
                                    echo "Labels: " . json_encode($labels) . "\n";
                                    echo "Data: " . json_encode($data);
                                    ?>
                                </pre>
                            </div>
                        </div>

                        <div class="col-md-5 p-4">
                            <h6>Top 3 Ruangan Paling Sering Dipinjam</h6>
                            <ul class="p-0 m-0 mt-3">
                                <?php
                                $bg_class = ['primary', 'info', 'warning'];
                                if (!empty($ruangan_favorit)):
                                    foreach ($ruangan_favorit as $index => $ruangan):
                                        $color = $bg_class[$index % count($bg_class)];
                                        $icon_name = ($index == 0) ? 'chalkboard' : (($index == 1) ? 'laptop' : 'users');
                                ?>
                                        <li class="d-flex mb-3 pb-1">
                                            <div class="avatar flex-shrink-0 me-3">
                                                <span class="avatar-initial rounded bg-label-<?= $color; ?>">
                                                    <i class="bx bx-<?= $icon_name; ?>"></i>
                                                </span>
                                            </div>
                                            <div class="d-flex w-100 justify-content-between">
                                                <div>
                                                    <h6 class="mb-0"><?= htmlspecialchars($ruangan['nama']); ?></h6>
                                                    <small class="text-muted">Total: **<?= $ruangan['frekuensi']; ?>** kali</small>
                                                </div>
                                                <span class="badge bg-label-<?= $color; ?>">
                                                    #<?= $index + 1; ?>
                                                </span>
                                            </div>
                                        </li>
                                    <?php endforeach;
                                else: ?>
                                    <p class="text-muted text-center py-3">Data ruangan favorit belum tersedia.</p>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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


    <div class="row">

        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="m-0">5 Riwayat Peminjaman Ruangan Terakhir</h5>
                    <small class="text-muted">Status permintaan terbaru Anda</small>
                </div>

                <div class="card-body">
                    <?php if (!empty($riwayat_peminjaman_terakhir)): ?>
                        <ul class="p-0 m-0">
                            <?php foreach ($riwayat_peminjaman_terakhir as $item):
                                // Menentukan warna badge berdasarkan kolom `status` ENUM
                                $badge_color = 'secondary';
                                if ($item['status'] == 'disetujui') {
                                    $badge_color = 'success';
                                } elseif ($item['status'] == 'ditolak') {
                                    $badge_color = 'danger';
                                } elseif ($item['status'] == 'menunggu') {
                                    $badge_color = 'warning';
                                } elseif ($item['status'] == 'dibatalkan') {
                                    $badge_color = 'secondary';
                                } else {
                                    $badge_color = 'info'; // Status 'selesai'
                                }
                            ?>
                                <li class="d-flex mb-4 pb-1">
                                    <div class="avatar flex-shrink-0 me-3">
                                        <span class="avatar-initial rounded bg-label-<?= $badge_color; ?>">
                                            <i class="bx bx-calendar-check"></i>
                                        </span>
                                    </div>

                                    <div class="d-flex w-100 justify-content-between">
                                        <div>
                                            <h6 class="mb-0">
                                                <?= htmlspecialchars($item['nama_ruangan'] ?? 'Ruangan Tidak Ditemukan'); ?>
                                            </h6>
                                            <small class="text-muted d-block">
                                                <?= $item['tanggal_pinjam']; ?>, Pukul **<?= $item['jam_mulai']; ?>** - **<?= $item['jam_selesai']; ?>**
                                            </small>
                                            <small class="text-truncate d-block">
                                                Keperluan: *<?= htmlspecialchars($item['keperluan'] ?? '-'); ?>*
                                            </small>
                                        </div>

                                        <div class="text-end">
                                            <span class="badge bg-label-<?= $badge_color; ?> mb-1">
                                                <?= ucfirst($item['status']); ?>
                                            </span>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                Oleh: <?= htmlspecialchars($item['approved_by_nama'] ?? 'N/A'); ?>
                                            </small>
                                        </div>
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

        <div class="col-md-6 col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header">
                    <h5 class="m-0">Kontak Penting</h5>
                    <small class="text-muted">Hubungi untuk masalah peminjaman/akun</small>
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
                                        <a href="tel:<?= $kontak['kontak']; ?>" class="badge bg-label-primary align-self-center">Hubungi</a>
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