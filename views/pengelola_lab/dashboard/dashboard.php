<?php
$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
?>

<?php
// ===========================
// DATABASE CONNECTION
// ===========================
if (!isset($conn)) {
    $conn = new mysqli("localhost", "root", "", "db_pinrulab");
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }
}

function fetchAll($res) {
    $rows = [];
    while ($r = $res->fetch_assoc()) $rows[] = $r;
    return $rows;
}

// ===========================
// 1. TOTAL PEMINJAMAN BULAN INI
// ===========================
$q = "
    SELECT 
        MONTH(tanggal_pinjam) AS bulan,
        COUNT(*) AS total
    FROM peminjaman
    WHERE status = 'disetujui'
    GROUP BY MONTH(tanggal_pinjam)
";
$r = $conn->query($q)->fetch_assoc();
$total_bulan_ini = $r ? $r['total'] : 0;

// ===========================
// 2. LAB PALING SERING DIPINJAM (CHART)
// ===========================
$q = "
    SELECT l.nama_lab, COUNT(*) AS jumlah
    FROM peminjaman p
    LEFT JOIN laboratorium l ON p.lab_id = l.id_lab
    WHERE MONTH(p.tanggal_pinjam) = MONTH(CURDATE())
      AND YEAR(p.tanggal_pinjam) = YEAR(CURDATE())
    GROUP BY l.id_lab
    ORDER BY jumlah DESC
";
$top_labs = fetchAll($conn->query($q));

$chart_labels = array_column($top_labs, 'nama_lab');
$chart_values = array_column($top_labs, 'jumlah');

// ===========================
// 3. KETERSEDIAAN REALTIME
// ===========================
$q = "SELECT COUNT(*) AS total_lab FROM laboratorium";
$total_lab = $conn->query($q)->fetch_assoc()['total_lab'];

$q = "
    SELECT COUNT(*) AS occupied
    FROM peminjaman
    WHERE tanggal_pinjam = CURDATE()
      AND status = 'disetujui'
      AND CURTIME() BETWEEN jam_mulai AND jam_selesai
";
$occupied = $conn->query($q)->fetch_assoc()['occupied'];

$available = $total_lab - $occupied;

// ===========================
// 4. 5 PEMINJAMAN TERBARU (SUDAH PAKAI JOIN NAMA LAB)
// ===========================
$q = "
    SELECT p.*, l.nama_lab
    FROM peminjaman p
    LEFT JOIN laboratorium l ON p.lab_id = l.id_lab
    ORDER BY p.created_at DESC
    LIMIT 5
";
$latest = fetchAll($conn->query($q));

?>

<div class="container-xxl flex-grow-1 container-p-y">

  <!-- WELCOME CARD -->
  <div class="row">
    <div class="col-lg-8 mb-4">
      <div class="card">
        <div class="d-flex align-items-end row">
          <div class="col-sm-8">
            <div class="card-body">
              <h3 class="card-title text-primary">Selamat Datang, <?= $nama_user ?> 👋</h3>
              <p class="mb-3">Pantau aktivitas laboratorium dan peminjaman secara real-time.</p>
            </div>
          </div>
          <div class="col-sm-4 text-center">
            <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png" height="140">
          </div>
        </div>
      </div>
    </div>

    <!-- STAT CARDS -->
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

  <!-- CHART + ACTIVITY -->
  <div class="row">

    <!-- CHART -->
    <div class="col-lg-8 mb-4">
      <div class="card">
        <h5 class="card-header">Lab Paling Banyak Dipinjam (Bulan Ini)</h5>
        <div class="card-body">
          <div id="chartTopLab"></div>
        </div>
      </div>
    </div>

    <!-- LAST ACTIVITIES -->
    <div class="col-lg-4 mb-4">
      <div class="card">
        <h5 class="card-header">Peminjaman Terbaru</h5>
        <ul class="list-group list-group-flush">

          <?php foreach ($latest as $row): ?>
            <li class="list-group-item d-flex justify-content-between">
              <div>
                <strong><?= $row['nama_lab'] ?></strong><br>
                <small>
                  <?= $row['tanggal_pinjam'] ?> |
                  <?= $row['jam_mulai'] ?> - <?= $row['jam_selesai'] ?>
                </small>
              </div>

              <span class="badge
                <?= $row['status']=='disetujui'?'bg-success':'' ?>
                <?= $row['status']=='menunggu'?'bg-warning':'' ?>
                <?= $row['status']=='ditolak'?'bg-danger':'' ?>
              ">
                <?= $row['status'] ?>
              </span>
            </li>
          <?php endforeach; ?>

        </ul>
      </div>
    </div>

  </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>

<!-- APEXCHARTS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
  var options = {
    chart: { type: 'bar', height: 320 },
    series: [{
      name: "Jumlah Peminjaman",
      data: <?= json_encode($chart_values) ?>
    }],
    xaxis: {
      categories: <?= json_encode($chart_labels) ?>
    },
    colors: ['#696cff'],
    plotOptions: {
      bar: { borderRadius: 4, horizontal: false }
    }
  };

  var chart = new ApexCharts(document.querySelector("#chartTopLab"), options);
  chart.render();
</script>
