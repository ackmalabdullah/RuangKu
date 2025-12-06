<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$nama_user    = $_SESSION['nama'] ?? 'Mahasiswa';
$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 0;

$conn = new mysqli("localhost", "root", "", "db_pinrulab");
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// === STATISTIK ===
$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND status = 'disetujui' AND tanggal_pinjam >= CURDATE()");
$stmt->bind_param("i", $id_mahasiswa); $stmt->execute();
$peminjaman_aktif = $stmt->get_result()->fetch_row()[0] ?? 0; $stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND status = 'menunggu'");
$stmt->bind_param("i", $id_mahasiswa); $stmt->execute();
$peminjaman_menunggu = $stmt->get_result()->fetch_row()[0] ?? 0; $stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE mahasiswa_id = ? AND (status = 'ditolak' OR status = 'dibatalkan')");
$stmt->bind_param("i", $id_mahasiswa); $stmt->execute();
$peminjaman_ditolak = $stmt->get_result()->fetch_row()[0] ?? 0; $stmt->close();

// === RIWAYAT ===
$stmt = $conn->prepare("SELECT p.tanggal_pinjam, p.jam_mulai, p.jam_selesai, p.keperluan, p.status, r.nama_ruangan 
                        FROM peminjaman p 
                        LEFT JOIN ruangan r ON p.ruangan_id = r.id_ruangan 
                        WHERE p.mahasiswa_id = ? 
                        ORDER BY p.created_at DESC LIMIT 6");
$stmt->bind_param("i", $id_mahasiswa); $stmt->execute();
$riwayat = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();

// === FAVORIT ===
$stmt = $conn->prepare("SELECT r.nama_ruangan, COUNT(*) AS kali 
                        FROM peminjaman p 
                        JOIN ruangan r ON p.ruangan_id = r.id_ruangan 
                        WHERE p.mahasiswa_id = ? 
                        GROUP BY r.id_ruangan 
                        ORDER BY kali DESC LIMIT 3");
$stmt->bind_param("i", $id_mahasiswa); $stmt->execute();
$favorit = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); $stmt->close();
?>

<!-- PARTICLE BACKGROUND (ringan) -->
<div id="particles-js" class="position-fixed top-0 start-0 w-100 h-100 opacity-15 pointer-events-none"></div>

<style>
  :root { --primary: #696cff; --primary-light: #e0e2ff; }
  body { background: linear-gradient(135deg, #f8f9ff 0%, #eef2ff 100%); overflow-x: hidden; }
  #particles-js { z-index: 1; }

  .dashboard-wrapper {
    position: relative;
    z-index: 2;
    padding: 2rem 1.5rem;
    max-width: 1500px;
    margin: 0 auto;
  }

  /* HERO CARD - Glassmorphism */
  .hero-card {
    background: rgba(255,255,255,0.38);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 1.8rem;
    box-shadow: 0 20px 50px rgba(105,108,255,0.22);
    overflow: hidden;
  }

  .circle-decor {
    position: absolute;
    width: 380px; height: 380px;
    background: linear-gradient(135deg, #696cff22, #a3b5ed11);
    border-radius: 50%;
    top: -60px; right: -60px;
    z-index: -1;
  }

  .stat-card {
    background: linear-gradient(135deg, #696cff, #8b9eff);
    border-radius: 1.5rem;
    padding: 1.8rem;
    color: white;
    text-align: center;
    box-shadow: 0 12px 30px rgba(105,108,255,0.35);
  }

  .shortcut-card {
    background: rgba(255,255,255,0.45);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.5);
    border-radius: 1.5rem;
    padding: 1.8rem;
    text-align: center;
    transition: all 0.4s ease;
  }
  .shortcut-card:hover {
    transform: translateY(-15px) scale(1.05);
    box-shadow: 0 25px 50px rgba(105,108,255,0.3);
  }
  .shortcut-icon {
    font-size: 3.5rem;
    background: linear-gradient(135deg, #696cff, #a3b5ed);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.8rem;
  }

  .data-card {
    background: white;
    border-radius: 1.5rem;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    overflow: hidden;
  }

  /* RESPONSIVE KHUSUS */
  @media (max-width: 992px) {
    .dashboard-wrapper { padding: 1.5rem 1rem; }
    .hero-card { border-radius: 1.6rem; }
  }
  @media (max-width: 576px) {
    .dashboard-wrapper { padding: 1rem 0.8rem; }
    .hero-card { border-radius: 1.4rem; }
    .btn-lg { font-size: 1.1rem !important; padding: 0.9rem 1.5rem !important; }
    .circle-decor { display: none; }
  }
</style>

<div class="dashboard-wrapper">

  <!-- HERO SECTION - 100% RESPONSIVE -->
  <div class="row g-4 g-xl-5 mb-5 align-items-center">
    <div class="col-lg-8 order-2 order-lg-1">
      <div class="card hero-card border-0 position-relative overflow-hidden">
        <div class="circle-decor d-none d-lg-block"></div>
        <div class="card-body p-4 p-md-5">
          <div class="text-center text-lg-start">
            <h1 class="display-5 display-lg-4 fw-bold text-primary mb-3">
              Halo, <?= htmlspecialchars($nama_user) ?>!
            </h1>
            <p class="fs-5 fs-lg-3 text-dark opacity-85 mb-4">
              Siap pinjam ruangan favoritmu hari ini?
            </p>
            <div class="d-grid d-lg-block">
              <a href="../peminjaman/ajukan.php" 
                 class="btn btn-primary btn-lg px-5 py-3 rounded-pill shadow-lg w-100 w-lg-auto">
                + Ajukan Sekarang
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- GAMBAR - RESPONSIVE & CANTIK -->
    <div class="col-lg-4 order-1 order-lg-2 text-center mb-4 mb-lg-0">
      <div class="position-relative d-inline-block">
        <img src="../../../assets/assets_dashboard/assets/img/illustrations/man-with-laptop-light.png"
             class="img-fluid"
             style="max-height: 340px; max-width: 90%; filter: drop-shadow(0 15px 35px rgba(105,108,255,0.25));"
             alt="Mahasiswa dengan Laptop">
      </div>
    </div>
  </div>

  <!-- STAT CARD -->
  <div class="row g-4 mb-5">
    <div class="col-lg-4 col-md-6">
      <div class="stat-card h-100">
        <i class="bx bx-check-circle display-4 mb-2"></i>
        <h2 class="display-5 fw-bold mb-1"><?= $peminjaman_aktif ?></h2>
        <p class="mb-0 opacity-90">Aktif Hari Ini</p>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="stat-card h-100" style="background: linear-gradient(135deg, #ffab00, #ffcd39);">
        <i class="bx bx-time-five display-4 mb-2"></i>
        <h2 class="display-5 fw-bold mb-1"><?= $peminjaman_menunggu ?></h2>
        <p class="mb-0 opacity-90">Menunggu</p>
      </div>
    </div>
    <div class="col-lg-4 col-md-6">
      <div class="stat-card h-100" style="background: linear-gradient(135deg, #ff3e1d, #ff6b6b);">
        <i class="bx bx-x-circle display-4 mb-2"></i>
        <h2 class="display-5 fw-bold mb-1"><?= $peminjaman_ditolak ?></h2>
        <p class="mb-0 opacity-90">Ditolak</p>
      </div>
    </div>
  </div>

  <!-- SHORTCUT MENU -->
  <div class="row g-4 mb-5">
    <?php 
    $menus = [
      ['link' => '../peminjaman/peminjaman.php', 'icon' => 'bx-home-smile', 'text' => 'Cari Ruangan'],
      ['link' => '../peminjaman/ajukan.php', 'icon' => 'bx-plus-circle', 'text' => 'Ajukan Pinjam'],
      ['link' => '../riwayat/riwayat.php', 'icon' => 'bx-history', 'text' => 'Riwayat'],
      ['link' => '../profile/profile.php', 'icon' => 'bx-user-circle', 'text' => 'Profil Saya'],
      ['link' => '../profile/ganti_password.php', 'icon' => 'bx-lock-alt', 'text' => 'Ganti Password']
    ];
    foreach($menus as $m): ?>
      <div class="col-6 col-md-4 col-lg">
        <a href="<?= $m['link'] ?>" class="text-decoration-none">
          <div class="card shortcut-card h-100">
            <i class="bx <?= $m['icon'] ?> shortcut-icon"></i>
            <h6 class="mb-0 text-dark fw-bold"><?= $m['text'] ?></h6>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- RIWAYAT + FAVORIT -->
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="card data-card">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
          <h5 class="fw-bold text-primary mb-0">Riwayat Terakhir</h5>
        </div>
        <div class="card-body p-0">
          <?php if ($riwayat): ?>
            <?php foreach ($riwayat as $i => $r): ?>
              <div class="p-4 <?= $i < count($riwayat)-1 ? 'border-bottom' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($r['nama_ruangan'] ?? 'Ruangan Tidak Diketahui') ?></h6>
                    <small class="text-muted">
                      <?= date('d M Y', strtotime($r['tanggal_pinjam'])) ?> • 
                      <?= substr($r['jam_mulai'],0,5) ?> - <?= substr($r['jam_selesai'],0,5) ?>
                    </small>
                    <p class="mb-0 mt-1 small text-muted">Keperluan: <?= htmlspecialchars($r['keperluan']) ?></p>
                  </div>
                  <span class="badge rounded-pill px-3 py-2 <?= $r['status']=='disetujui' ? 'bg-success' : ($r['status']=='menunggu' ? 'bg-warning text-dark' : 'bg-danger') ?>">
                    <?= ucfirst($r['status']) ?>
                  </span>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center py-5 text-muted">
              <i class="bx bx-calendar-x display-1 opacity-20"></i>
              <p class="mt-3">Belum ada riwayat peminjaman</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card data-card h-100">
        <div class="card-header bg-transparent border-0 pt-4 px-4">
          <h5 class="fw-bold text-primary mb-0">Ruangan Favorit</h5>
        </div>
        <div class="card-body">
          <?php if ($favorit): ?>
            <ol class="list-group list-group-numbered">
              <?php foreach ($favorit as $f): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                  <?= htmlspecialchars($f['nama_ruangan']) ?>
                  <span class="badge bg-primary rounded-pill"><?= $f['kali'] ?>×</span>
                </li>
              <?php endforeach; ?>
            </ol>
          <?php else: ?>
            <p class="text-center text-muted py-5 mb-0">Belum ada favorit</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- PARTICLES JS -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
<script>
  particlesJS("particles-js", {
    particles: {
      number: { value: 50, density: { enable: true, value_area: 800 } },
      color: { value: "#696cff" },
      opacity: { value: 0.3, random: true },
      size: { value: 3, random: true },
      move: { enable: true, speed: 1.2 }
    },
    interactivity: { events: { onhover: { enable: true, mode: "repulse" } } },
    retina_detect: true
  });
</script>

<?php require '../../../partials/mahasiswa/footer.php'; ?>