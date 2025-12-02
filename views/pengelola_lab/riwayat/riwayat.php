<?php
$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$db = new Database();
$koneksi = $db->conn;

$query = "
  SELECT 
      p.id_peminjaman,
      m.nama AS nama_mahasiswa,
      l.nama_lab,
      p.tanggal_pinjam,
      p.jam_mulai,
      p.jam_selesai,
      p.keperluan,
      p.jumlah_peserta,
      p.status,
      p.created_at
  FROM peminjaman p
  LEFT JOIN mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa
  LEFT JOIN laboratorium l ON p.lab_id = l.id_lab
  WHERE p.lab_id IS NOT NULL
  ORDER BY p.created_at DESC
";

$result = mysqli_query($koneksi, $query);
?>

<div class="container mt-4 mb-5">
    <h4 class="fw-bold mb-3">📋 Riwayat Peminjaman Laboratorium</h4>

    <?php
    if (isset($_SESSION['alert'])) {
        $a = $_SESSION['alert'];
        echo "<script>
        Swal.fire({
            icon: '{$a['icon']}',
            title: '{$a['title']}',
            html: '{$a['text']}'
        });
    </script>";
        unset($_SESSION['alert']);
    }
    ?>

    <!-- Tombol Cetak -->
    <div class="mb-3 text-end">
        <a href="cetak_pdf.php" target="_blank" class="btn btn-primary">
            <i class="bx bx-printer"></i> Cetak PDF
        </a>
    </div>

    <div class="card shadow-sm p-3 rounded-3">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Laboratorium</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Keperluan</th>
                        <th>Peserta</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        $no = 1;

                        while ($row = mysqli_fetch_assoc($result)) {

                            $status_value = trim($row['status'] ?? '');
                            if ($status_value == '') $status_value = 'status_invalid';

                            $badge = match ($status_value) {
                                'menunggu' => 'warning',
                                'disetujui' => 'success',
                                'ditolak' => 'danger',
                                'dibatalkan' => 'secondary',
                                'status_invalid' => 'dark',
                                default => 'info'
                            };

                            $display_status = ($status_value === 'status_invalid') ? 'Cek Data' : $status_value;

                            echo "
              <tr>
                <td>{$no}</td>
                <td>{$row['nama_mahasiswa']}</td>
                <td>{$row['nama_lab']}</td>
                <td>{$row['tanggal_pinjam']}</td>
                <td>{$row['jam_mulai']} - {$row['jam_selesai']}</td>
                <td>{$row['keperluan']}</td>
                <td>{$row['jumlah_peserta']}</td>
                <td><span class='badge bg-{$badge} text-capitalize'>{$display_status}</span></td>
                <td>
                  <a href='detail.php?id={$row['id_peminjaman']}' class='btn btn-sm btn-info me-1'>Detail</a>
                  <a href='setujui.php?id={$row['id_peminjaman']}' class='btn btn-sm btn-success me-1'>Setujui</a>
                  <a href='tolak.php?id={$row['id_peminjaman']}' class='btn btn-sm btn-danger'>Tolak</a>
                </td>
              </tr>
            ";

                            $no++;
                        }
                    } else {
                        echo "
            <tr>
              <td colspan='9' class='text-center text-muted py-4'>
                Belum ada riwayat peminjaman laboratorium.
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
