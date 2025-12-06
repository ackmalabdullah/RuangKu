<?php
$required_role = 'jurusan';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Ambil SESSION
$nama_user    = $_SESSION['nama'] ?? 'Jurusan';
$id_user      = $_SESSION['id_user'] ?? 0;
$nama_jurusan = $_SESSION['nama'] ?? 'Jurusan';

// Koneksi Database
$conn = new mysqli("localhost", "root", "", "db_pinrulab");
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

/* ================================================================
   VALIDASI ROLE
================================================================ */
$q = "SELECT id_user FROM users WHERE id_user = ? AND role = 'jurusan' LIMIT 1";
$stmt = $conn->prepare($q);
if (!$stmt) {
  die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
  die("Akses ditolak. Anda bukan jurusan.");
}
$stmt->close();

/* ================================================================
   1. TOTAL PEMINJAMAN DISETUJUI BULAN INI
================================================================ */
$q = "SELECT COUNT(*) AS total 
      FROM peminjaman
      WHERE status_jurusan = 'disetujui'
        AND jurusan_approved_by = ?
        AND MONTH(tanggal_pinjam) = MONTH(CURDATE())
        AND YEAR(tanggal_pinjam)  = YEAR(CURDATE())";

$stmt = $conn->prepare($q);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$total_acc = (int)($row['total'] ?? 0);
$stmt->close();

/* ================================================================
   2. TOTAL LABORATORIUM
================================================================ */
$q = "SELECT COUNT(*) AS jumlah_lab FROM laboratorium";
$res = $conn->query($q);
$jumlah_lab = 0;

if ($res && $res->num_rows > 0) {
    $jumlah_lab = (int)$res->fetch_assoc()['jumlah_lab'];
}
?>

<!-- =========================== -->
<!--   HALAMAN DASHBOARD         -->
<!-- =========================== -->

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- Welcome -->
  <div class="row mb-4">
    <div class="col-12">
      <div class="card bg-primary text-white shadow-none">
        <div class="card-body d-flex align-items-center justify-content-between flex-wrap">
          <div>
            <h2 class="text-white mb-1">Selamat Datang, <?= htmlspecialchars($nama_jurusan) ?>!</h2>
            <p class="mb-0 opacity-90">Dashboard Jurusan – Pantau data laboratorium & peminjaman</p>
          </div>
          <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png"
            height="130" class="d-none d-lg-block" alt="Jurusan">
        </div>
      </div>
    </div>
  </div>

  <div class="row g-4">

    <!-- Total Peminjaman Disetujui Bulan Ini -->
    <div class="col-lg-6 col-md-6 col-12">
      <div class="card h-100 text-center border-0 shadow-sm">
        <div class="card-body">
          <div class="avatar bg-label-success rounded mb-3 mx-auto" style="width:80px;height:80px;">
            <i class="bx bx-check-double bx-lg"></i>
          </div>
          <h5 class="mb-2">Peminjaman Disetujui</h5>
          <h1 class="text-success mb-1"><?= number_format($total_acc) ?></h1>
          <small class="text-muted">Bulan <?= date('F Y') ?></small>
        </div>
      </div>
    </div>

    <!-- Total Laboratorium -->
    <div class="col-lg-6 col-md-6 col-12">
      <div class="card h-100 text-center border-0 shadow-sm">
        <div class="card-body">
          <div class="avatar bg-label-info rounded mb-3 mx-auto" style="width:80px;height:80px;">
            <i class="bx bx-building bx-lg"></i>
          </div>
          <h5 class="mb-2">Total Laboratorium</h5>
          <h1 class="text-info mb-1"><?= number_format($jumlah_lab) ?></h1>
          <small class="text-muted">Keseluruhan Laboratorium</small>
        </div>
      </div>
    </div>

  </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>
