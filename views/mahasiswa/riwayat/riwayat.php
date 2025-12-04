<?php
// Pastikan semua file partials dan koneksi sudah benar
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$db = new Database();
$koneksi = $db->conn;

$id_mahasiswa = $_SESSION['id_mahasiswa'] ?? 0;

// =========================================================
// QUERY: Mengambil riwayat, menentukan tipe, dan nama entitas
// =========================================================
$query = "
    SELECT 
        p.id_peminjaman AS id_peminjaman,
        p.ruangan_id, 
        p.lab_id, 
        -- Tentukan tipe berdasarkan prioritas lab > ruangan
        CASE 
            WHEN p.lab_id IS NOT NULL AND p.lab_id > 0 
                THEN 'Laboratorium'
            WHEN p.ruangan_id IS NOT NULL AND p.ruangan_id > 0 
                THEN 'Ruangan'
            ELSE 'Tidak Diketahui'
        END AS tipe,
        -- Nama entitas sesuai tipe
        CASE 
            WHEN p.lab_id IS NOT NULL AND p.lab_id > 0 
                THEN l.nama_lab
            WHEN p.ruangan_id IS NOT NULL AND p.ruangan_id > 0 
                THEN r.nama_ruangan
            ELSE 'Tidak Diketahui'
        END AS nama_entitas,
        p.tanggal_pinjam, 
        p.jam_mulai, 
        p.jam_selesai AS jam_selesai,
        p.keperluan AS keperluan,
        p.jumlah_peserta AS jumlah_peserta,
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
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<script>
            Swal.fire({
                icon: '{$alert['icon']}',
                title: '{$alert['title']}',
                html: '{$alert['text']}',
                confirmButtonColor: '#3085d6'
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
                        <th class="text-center">Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        $no = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            $status_db = trim($row['status'] ?? '');
                            $tipe = $row['tipe'];
                            $aksi_button = ''; 

                            // Mapping Status ke Badge
                            $badge_map = [
                                'menunggu' => ['badge' => 'warning', 'display' => 'Menunggu'],
                                'disetujui' => ['badge' => 'success', 'display' => 'Disetujui'],
                                'ditolak' => ['badge' => 'danger', 'display' => 'Ditolak'],
                                'dibatalkan' => ['badge' => 'secondary', 'display' => 'Dibatalkan'],
                                'selesai' => ['badge' => 'info', 'display' => 'Selesai']
                            ];
                            
                            $status_info = $badge_map[strtolower($status_db)] ?? ['badge' => 'dark', 'display' => 'Cek Data'];
                            $badge = $status_info['badge'];
                            $display_status = $status_info['display'];

                            if ($status_db === 'disetujui' && $tipe === 'Ruangan') {
                                // Tombol cetak ruangan (btn-primary)
                                $aksi_button = "
                                    <a href='cetak_surat_ruangan.php?id={$row['id_peminjaman']}' target='_blank' class='btn btn-sm btn-primary' title='Cetak Surat Pemberitahuan'>
                                        <i class='bi bi-file-earmark-text-fill me-1'></i> Cetak Surat
                                    </a>
                                ";
                            } elseif ($status_db === 'disetujui' && $tipe === 'Laboratorium') {
                                // Tombol cetak laboratorium (btn-info)
                                $aksi_button = "
                                    <a href='cetak_surat_lab.php?id={$row['id_peminjaman']}' target='_blank' class='btn btn-sm btn-info' title='Cetak Surat Laboratorium'>
                                        <i class='bi bi-file-earmark-text-fill me-1'></i> Cetak Surat
                                    </a>
                                ";
                            }

                            // Format waktu
                            $jam_mulai = date('H:i', strtotime($row['jam_mulai']));
                            $jam_selesai = date('H:i', strtotime($row['jam_selesai']));
                            $waktu = $jam_mulai . ' - ' . $jam_selesai;

                            echo "
                                <tr>
                                    <td>{$no}</td>
                                    <td>{$tipe}</td>
                                    <td>{$row['nama_entitas']}</td>
                                    <td>{$row['tanggal_pinjam']}</td>
                                    <td>{$waktu}</td>
                                    <td>{$row['keperluan']}</td>
                                    <td>{$row['jumlah_peserta']}</td>
                                    <td><span class='badge bg-{$badge}'>{$display_status}</span></td>
                                    <td class='text-center'>{$aksi_button}</td>      
                                </tr>";
                            $no++;
                        }
                    } else {
                        echo "
                            <tr>
                                <td colspan='9' class='text-center text-muted py-4'>
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