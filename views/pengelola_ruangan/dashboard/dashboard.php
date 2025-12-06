<?php
$required_role = 'pengelola_ruangan';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$nama_user = $_SESSION['nama'] ?? 'User';

$conn = new mysqli("localhost", "root", "", "db_pinrulab");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

function fetchAll($res)
{
  $rows = [];
  while ($row = $res->fetch_assoc()) $rows[] = $row;
  return $rows;
}

// 1. TOTAL PEMINJAMAN BULAN INI
$total_bulan_ini = $conn->query("
    SELECT COUNT(*) AS t 
    FROM peminjaman
    WHERE status = 'disetujui' 
      AND MONTH(tanggal_pinjam) = MONTH(CURDATE())
      AND YEAR(tanggal_pinjam)  = YEAR(CURDATE())
")->fetch_assoc()['t'] ?? 0;

// 2. TOP 5 RUANGAN TERBANYAK DIPINJAM
$top = fetchAll($conn->query("
    SELECT r.nama_ruangan, COUNT(*) AS jumlah
    FROM peminjaman pr
    JOIN ruangan r ON pr.ruangan_id = r.id_ruangan
    WHERE MONTH(pr.tanggal_pinjam) = MONTH(CURDATE())
      AND YEAR(pr.tanggal_pinjam)  = YEAR(CURDATE())
    GROUP BY r.id_ruangan
    ORDER BY jumlah DESC
    LIMIT 5
"));
$chart_labels = $top ? array_column($top, 'nama_ruangan') : ['Belum Ada Data'];
$chart_values = $top ? array_column($top, 'jumlah')       : [0];

// 3. TOTAL RUANGAN & TERSEDIA
$total_ruangan = $conn->query("SELECT COUNT(*) AS t FROM ruangan")->fetch_assoc()['t'] ?? 0;
$sedang_digunakan = $conn->query("
    SELECT COUNT(*) AS o
    FROM peminjaman
    WHERE tanggal_pinjam = CURDATE()
      AND status = 'disetujui'
      AND CURTIME() BETWEEN jam_mulai AND jam_selesai
")->fetch_assoc()['o'] ?? 0;
$tersedia = $total_ruangan - $sedang_digunakan;

// 4. PEMINJAMAN TERBARU
$terbaru = fetchAll($conn->query("
    SELECT pr.*, r.nama_ruangan
    FROM peminjaman pr
    JOIN ruangan r ON pr.ruangan_id = r.id_ruangan
    ORDER BY pr.created_at DESC
    LIMIT 8
"));
?>

<style>
  .dashboard-wrapper {
    padding: 2.5rem 2rem;
    max-width: 1500px;
    margin: 0 auto;
  }

  @media (min-width: 1400px) {
    .dashboard-wrapper {
      padding: 3.5rem 3rem;
    }
  }

  .mb-section {
    margin-bottom: 3.5rem !important;
  }

  .welcome-card {
    background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%);
    border-radius: 1.5rem;
    box-shadow: 0 10px 30px rgba(105, 108, 255, 0.12);
  }

  .shortcut-card {
    background: white;
    border-radius: 1.3rem;
    padding: 2rem 1rem;
    text-align: center;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
    height: 100%;
  }

  .shortcut-card:hover {
    transform: translateY(-12px);
    box-shadow: 0 20px 40px rgba(105, 108, 255, 0.25);
  }

  .shortcut-icon {
    font-size: 3rem;
    color: #696cff;
    margin-bottom: 0.8rem;
  }
</style>

<div class="dashboard-wrapper">

  <!-- WELCOME + STATISTIK -->
  <div class="row g-4 mb-section">
    <!-- Welcome Card -->
    <div class="col-lg-8">
      <div class="card welcome-card border-0 h-100">
        <div class="card-body p-4">
          <div class="row align-items-center">
            <div class="col-md-8">
              <h2 class="fw-bold text-primary mb-2">Selamat Datang, <?= htmlspecialchars($nama_user) ?>!</h2>
              <p class="text-muted fs-5 mb-0">Pantau aktivitas peminjaman ruangan secara real-time.</p>
            </div>
            <div class="col-md-4 text-center text-md-end mt-4 mt-md-0">
              <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png"
                class="img-fluid" style="max-height:190px;" alt="welcome">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CARD UNGU (SUDAH JELAS BANGET TEKSNYA) -->
    <div class="col-lg-4">
      <div class="row g-4 h-100">
        <div class="col-6">
          <div class="card h-100 text-white text-center" style="background: linear-gradient(135deg, #a3b5edff, #cecbd7ff); border-radius: 1.3rem; box-shadow: 0 12px 30px rgba(94,114,228,0.4);">
            <div class="card-body py-5">
              <h6 class="mb-3 opacity-90">Total Peminjaman</h6>
              <h2 class="mb-2 display-5 fw-bold"><?= $total_bulan_ini ?></h2>
              <small class="opacity-80">Bulan ini</small>
            </div>
          </div>
        </div>

        <div class="col-6">
          <div class="card h-100 text-white text-center" style="background: linear-gradient(135deg, #a3b5edff, #cecbd7ff); border-radius: 1.3rem; box-shadow: 0 12px 30px rgba(94,114,228,0.4);">
            <div class="card-body py-5">
              <h6 class="mb-3 opacity-90">Ruangan Tersedia</h6>
              <h2 class="mb-2 display-5 fw-bold"><?= $tersedia ?>/<?= $total_ruangan ?></h2>
              <small class="opacity-80">Realtime</small>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- SHORTCUT 5 KOLOM -->
  <div class="row g-4 mb-section">
    <?php
    $menus = [
      ['Ruangan',      'bx-building-house',   '../ruangan/ruangan.php'],
      ['Persetujuan',            'bx-check-circle',     '../persetujuan/persetujuan.php'],
      ['Laporan Ruangan',        'bx-file',             '../laporan/laporan_ruangan.php'],
      ['Laporan Peminjaman',     'bx-notepad',          '../laporan/laporan_peminjaman.php'],
      ['Riwayat',                'bx-history',          '../riwayat/riwayat.php']
    ];
    foreach ($menus as $m): ?>
      <div class="col">
        <a href="<?= $m[2] ?>" class="text-decoration-none">
          <div class="card shortcut-card border-0">
            <i class="bx <?= $m[1] ?> shortcut-icon"></i>
            <h6 class="mb-0 text-dark fw-600"><?= $m[0] ?></h6>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- CHART + LIST TERBARU -->
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header bg-transparent border-0 pt-4">
          <h5 class="mb-0 fw-bold">Ruangan Paling Banyak Dipinjam Bulan Ini</h5>
        </div>
        <div class="card-body">
          <canvas id="chartRuangan" height="340"></canvas>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card h-100 shadow-sm">
        <div class="card-header bg-transparent border-0">
          <h5 class="mb-0 fw-bold">Peminjaman Ruangan Terbaru</h5>
        </div>
        <div class="card-body p-0">
          <?php if ($terbaru): ?>
            <?php foreach ($terbaru as $i => $r): ?>
              <div class="px-4 py-3 <?= $i < count($terbaru) - 1 ? 'border-bottom' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold text-dark"><?= htmlspecialchars($r['nama_ruangan']) ?></div>
                    <small class="text-muted">
                      <?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?> | <?= $r['jam_mulai'] ?> - <?= $r['jam_selesai'] ?>
                    </small>
                  </div>
                  <span class="badge rounded-pill px-3 py-2 <?=
                                                            $r['status'] == 'disetujui' ? 'bg-success' : ($r['status'] == 'menunggu' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                    <?= strtoupper($r['status']) ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center py-5 text-muted">Belum ada peminjaman</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  document.addEventListener("DOMContentLoaded", () => {
    new Chart(document.getElementById('chartRuangan'), {
      type: 'bar',
      data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
          data: <?= json_encode($chart_values) ?>,
          backgroundColor: '#696cff',
          borderRadius: 12,
          maxBarThickness: 55
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  });
</script>