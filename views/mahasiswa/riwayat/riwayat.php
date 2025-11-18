<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// --- koneksi ---
// require '../../../settings/koneksi.php';
$db = new Database();
$koneksi = $db->conn;

// --- ambil data peminjaman berdasarkan mahasiswa yang login ---
// session_start();
$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 0;

$query = "
  SELECT 
      p.id_peminjaman,
      COALESCE(r.nama_ruangan, l.nama_lab) AS nama_entitas,
      CASE 
          WHEN p.ruangan_id IS NOT NULL THEN 'Ruangan'
          WHEN p.lab_id IS NOT NULL THEN 'Laboratorium'
      END AS tipe,
      p.tanggal_pinjam, 
      p.jam_mulai, 
      p.jam_selesai, 
      p.keperluan, 
      p.jumlah_peserta, 
      p.status, 
      p.created_at
  FROM peminjaman p
  LEFT JOIN ruangan r ON p.ruangan_id = r.id_ruangan
  LEFT JOIN laboratorium l ON p.lab_id = l.id_lab
  WHERE p.mahasiswa_id = ?
  ORDER BY p.created_at DESC
";

$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, 'i', $id_mahasiswa);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<div class="container mt-4 mb-5">
  <h4 class="fw-bold mb-3">📋 Riwayat Peminjaman</h4>

  <?php
  // --- alert dari session ---
  if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo "<script>
          Swal.fire({
              icon: '{$alert['icon']}',
              title: '{$alert['title']}',
              html: '{$alert['text']}'
          });
      </script>";
    unset($_SESSION['alert']);
  }
  ?>

  <div class="card shadow-sm p-3 rounded-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>No</th>
            <th>Tipe</th>
            <th>Nama</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Keperluan</th>
            <th>Peserta</th>
            <th>Status</th>
          </tr>
        </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0) {
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
                
                // 1. Tentukan status dari DB. Jika NULL atau kosong, gunakan 'status_invalid'.
                $status_db = trim($row['status'] ?? ''); // Hilangkan spasi di awal/akhir
                if (empty($status_db)) {
                    $status_db = 'status_invalid';
                }

                $badge = match ($status_db) {
                    'menunggu' => 'warning',
                    'disetujui' => 'success',
                    'ditolak' => 'danger',
                    'dibatalkan' => 'secondary',
                    'status_invalid' => 'dark', // Status tidak valid/kosong
                    default => 'info' // Jika ada status baru selain daftar di atas
                };
                
                // 2. Tentukan teks yang akan ditampilkan di dalam badge
                $display_status = ($status_db === 'status_invalid') ? 'Cek Data' : $status_db;

                echo "
                    <tr>
                        <td>{$no}</td>
                        <td>{$row['tipe']}</td>
                        <td>{$row['nama_entitas']}</td>
                        <td>{$row['tanggal_pinjam']}</td>
                        <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
                        <td>{$row['keperluan']}</td>
                        <td>{$row['jumlah_peserta']}</td>
                        <td><span class='badge bg-{$badge} text-capitalize'>{$display_status}</span></td>
                    </tr>";
                $no++;
            }
        } 
         else {
            echo "
              <tr>
                  <td colspan='8' class='text-center text-muted py-4'>
                      Belum ada riwayat peminjaman.
                  </td>
              </tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
require '../../../partials/mahasiswa/footer.php';
?>