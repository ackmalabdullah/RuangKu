<?php
$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Nama user fallback
$nama_user = isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User';

$conn = new mysqli("localhost", "root", "", "db_pinrulab");
if ($conn->connect_error) die("Koneksi gagal");

// Helper ambil array row
function fetchAll($res)
{
  $rows = [];
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  return $rows;
}

// --- TOTAL PEMINJAMAN BULAN INI
$q = "
SELECT COUNT(*) AS total
FROM peminjaman
WHERE status='disetujui'
AND MONTH(tanggal_pinjam)=MONTH(CURDATE())
AND YEAR(tanggal_pinjam)=YEAR(CURDATE())";
$r = $conn->query($q)->fetch_assoc();
$total_bulan_ini = $r['total'] ?? 0;

// --- LAB PALING BANYAK DIPINJAM
$q = "
SELECT l.nama_lab, COUNT(*) AS jumlah
FROM peminjaman p
LEFT JOIN laboratorium l ON p.lab_id=l.id_lab
WHERE MONTH(p.tanggal_pinjam)=MONTH(CURDATE())
AND YEAR(p.tanggal_pinjam)=YEAR(CURDATE())
GROUP BY l.id_lab
ORDER BY jumlah DESC
LIMIT 5";
$top_labs = fetchAll($conn->query($q));

$chart_labels = array_column($top_labs, 'nama_lab');
$chart_values = array_column($top_labs, 'jumlah');

if (empty($chart_labels)) {
  $chart_labels = ["Belum Ada Data"];
  $chart_values = [0];
}

// --- LAB AVAILABLE
$total_lab = $conn->query("SELECT COUNT(*) AS total_lab FROM laboratorium")->fetch_assoc()['total_lab'];
$occupied = $conn->query("
 SELECT COUNT(*) AS occupied
 FROM peminjaman
 WHERE tanggal_pinjam=CURDATE()
 AND status='disetujui'
 AND CURTIME() BETWEEN jam_mulai AND jam_selesai
")->fetch_assoc()['occupied'];

$available = $total_lab - $occupied;

// --- DATA TERBARU
$q = "
SELECT p.*, l.nama_lab
FROM peminjaman p
LEFT JOIN laboratorium l ON p.lab_id=l.id_lab
ORDER BY p.created_at DESC
LIMIT 5";
$latest = fetchAll($conn->query($q));

?>

<style>
  .shortcut-card {
    border-radius: 12px;
    border: 1px solid #e6e6e6;
    transition: 0.2s ease;
    cursor: pointer;
  }

  .shortcut-card:hover {
    background-color: #f5f6ff;
    transform: translateY(-5px);
    box-shadow: 0px 8px 18px rgba(0, 0, 0, 0.15);
  }

  .shortcut-icon {
    font-size: 50px;
    color: #696cff;
  }

  .shortcut-link {
    text-decoration: none;
    color: inherit;
  }

  .shortcut-wrapper {
    margin-top: 10px;
    margin-bottom: 25px;
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 15px;
  }

  .shortcut-card {
    padding: 25px;
    text-align: center;
    border-radius: 12px;
    background: #fff;
    border: 1px solid #eee;
    transition: .2s;
  }

  .shortcut-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 18px rgba(0, 0, 0, 0.10);
  }

  .shortcut-icon {
    font-size: 32px;
    color: #6a65ff;
  }

  .shortcut-link {
    text-decoration: none;
    color: inherit;
  }
</style>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="row">
    <div class="col-lg-8 mb-4">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-8">
            <div class="card-body pb-1">
              <h3 class="card-title text-primary">Selamat Datang, <?= $nama_user ?> 👋</h3>
              <p>Pantau aktivitas laboratorium secara real-time.</p>
            </div>
          </div>
          <div class="col-sm-4 text-center">
            <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png" height="140">
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="row">
        <div class="col-6 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h6>Total Peminjaman</h6>
              <h3><?= $total_bulan_ini ?></h3>
              <small>Bulan ini</small>
            </div>
          </div>
        </div>

        <div class="col-6 mb-4">
          <div class="card text-center">
            <div class="card-body">
              <h6>Lab Tersedia</h6>
              <h3><?= $available ?>/<?= $total_lab ?></h3>
              <small>Realtime</small>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>


  <!-- SHORTCUT MENU ULTRA RAPIH -->
  <div class="shortcut-wrapper">

    <a href="../fasilitas_lab/fasilitas_lab.php" class="shortcut-link">
      <div class="card shortcut-card">
        <i class='bx bx-wrench shortcut-icon'></i>
        <h6 class="mt-2">Fasilitas Lab</h6>
      </div>
    </a>

    <a href="../laporan/laporan_lab.php" class="shortcut-link">
      <div class="card shortcut-card">
        <i class='bx bx-file shortcut-icon'></i>
        <h6 class="mt-2">Laporan Lab</h6>
      </div>
    </a>

    <a href="../laporan_peminjaman/laporan_peminjaman.php" class="shortcut-link">
      <div class="card shortcut-card">
        <i class='bx bx-notepad shortcut-icon'></i>
        <h6 class="mt-2">Laporan Peminjaman</h6>
      </div>
    </a>

    <a href="../persetujuan/persetujuan.php" class="shortcut-link">
      <div class="card shortcut-card">
        <i class='bx bx-check-circle shortcut-icon'></i>
        <h6 class="mt-2">Persetujuan</h6>
      </div>
    </a>

    <a href="../riwayat/riwayat.php" class="shortcut-link">
      <div class="card shortcut-card">
        <i class='bx bx-history shortcut-icon'></i>
        <h6 class="mt-2">Riwayat</h6>
      </div>
    </a>

  </div>



  <div class="row">

    <!-- CHART CARD -->
    <div class="col-lg-8 mb-4">
      <div class="card" style="height:330px;">
        <h5 class="card-header">Lab Paling Banyak Dipinjam Bulan Ini</h5>
        <div class="card-body" style="height:250px;">
          <canvas id="chartTopLab" style="height:100%;"></canvas>
        </div>
      </div>
    </div>

    <!-- LIST TERBARU -->
    <div class="col-lg-4 mb-4">
      <div class="card" style="height:330px;">
        <h5 class="card-header">Peminjaman Terbaru</h5>
        <ul class="list-group list-group-flush" style="overflow-y:auto; height:250px;">
          <?php foreach ($latest as $row): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div>
                <strong><?= $row['nama_lab'] ?></strong><br>
                <small><?= $row['tanggal_pinjam'] ?> | <?= $row['jam_mulai'] ?> - <?= $row['jam_selesai'] ?></small>
              </div>

              <span class="badge
                <?= $row['status'] == 'disetujui' ? 'bg-success' : '' ?>
                <?= $row['status'] == 'menunggu' ? 'bg-warning' : '' ?>
                <?= $row['status'] == 'ditolak' ? 'bg-danger' : '' ?>">
                <?= strtoupper($row['status']) ?>
              </span>

            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    new Chart(document.getElementById("chartTopLab"), {
      type: "bar",
      data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
          label: "Jumlah Peminjaman",
          data: <?= json_encode($chart_values) ?>,
          backgroundColor: "#74b3ff"
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    })
  });
</script>