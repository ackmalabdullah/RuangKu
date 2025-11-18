<?php
$required_role = 'pengelola_lab';

// Memuat komponen layout
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Catatan: Asumsi $koneksi sudah tersedia dari header.php 

// --- LOGIKA PENGAMBILAN DATA LABORATORIUM DENGAN FASILITAS ---

$query = "
    SELECT 
        l.id_lab, l.nama_lab, l.lokasi, l.kapasitas, l.status_lab, l.gambar, 
        kr.nama_kategori,
        
        -- Mengambil semua fasilitas dan jumlahnya ke dalam satu kolom
        GROUP_CONCAT(
            CONCAT(f.nama_fasilitas, ' (', lf.jumlah, ')')
            ORDER BY f.nama_fasilitas ASC
            SEPARATOR '<br>' -- Gunakan <br> agar setiap fasilitas tampil di baris baru
        ) AS daftar_fasilitas
        
    FROM 
        laboratorium l 
    LEFT JOIN 
        kategori_ruangan kr ON l.kategori_id = kr.id_kategori
    LEFT JOIN 
        lab_fasilitas lf ON l.id_lab = lf.lab_id -- Join ke tabel penghubung Lab-Fasilitas
    LEFT JOIN
        fasilitas f ON lf.fasilitas_id = f.id_fasilitas -- Join ke tabel fasilitas
    
    GROUP BY 
        l.id_lab, l.nama_lab, l.lokasi, l.kapasitas, l.status_lab, l.gambar, kr.nama_kategori
        
    ORDER BY 
        l.nama_lab ASC
";

$result = mysqli_query($koneksi, $query);

// Cek error query
if (!$result) {
    die("Query Error: " . mysqli_error($koneksi));
}
?>

<style>
    /* CSS Kustom untuk Tampilan Tabel */
    .table-hover thead th {
        /* Memastikan header terlihat jelas */
        white-space: nowrap;
    }

    .gambar-cell {
        /* Lebar spesifik untuk thumbnail */
        width: 80px;
        text-align: center;
    }

    .fasilitas-cell {
        /* Memastikan teks fasilitas menggunakan spasi normal (tidak dipaksa dalam satu baris) */
        white-space: normal;
        /* Memberi lebar minimum agar konten terlihat */
        min-width: 250px;
    }

    .aksi-cell {
        /* Memberi lebar minimum agar tombol tidak bertumpuk */
        min-width: 150px;
    }
</style>

<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card">
        <h5 class="card-header">Data Laboratorium</h5>
        <div class="card-body">

            <div class="mb-3">
                <a href="form_lab.php" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i> Tambah Data Laboratorium
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lab</th>
                            <th class="gambar-cell">Gambar</th>
                            <th>Kategori</th>
                            <th>Lokasi</th>
                            <th>Kapasitas</th>
                            <th class="fasilitas-cell">Fasilitas & Jumlah</th>
                            <th>Status</th>
                            <th class="aksi-cell">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($data = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?= $no++; ?></td>

                                    <td>
                                        <i class="bx bx-building-house fa-lg text-primary me-3"></i>
                                        <strong><?= htmlspecialchars($data['nama_lab']); ?></strong>
                                    </td>

                                    <td class="gambar-cell">
                                        <?php
                                        // ASUMSI PATH GAMBAR: Ubah jika path Anda berbeda
                                        $gambar_path = 'assets/img/lab/' . htmlspecialchars($data['gambar']);

                                        // Pastikan ada nama file gambar di database
                                        if (!empty($data['gambar'])) :
                                        ?>
                                            <img src="../../../<?= $gambar_path; ?>"
                                                alt="Foto Laboratorium"
                                                class="img-thumbnail"
                                                style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php else : ?>
                                            <span class="text-muted" style="font-size: 0.75rem;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="bx bx-tag fa-lg text-info me-3"></i>
                                        <?= htmlspecialchars($data['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                    </td>

                                    <td>
                                        <i class="bx bx-map fa-lg text-warning me-3"></i>
                                        <?= htmlspecialchars($data['lokasi']); ?>
                                    </td>

                                    <td>
                                        <i class="bx bx-user-check fa-lg text-success me-3"></i>
                                        <?= htmlspecialchars($data['kapasitas']); ?> Orang
                                    </td>

                                    <td class="fasilitas-cell">
                                        <?php
                                        if ($data['daftar_fasilitas']) {
                                            echo $data['daftar_fasilitas'];
                                        } else {
                                            echo '<span class="text-muted">Tidak ada fasilitas</span>';
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        $status = $data['status_lab'];
                                        $badge_class = 'bg-label-secondary';
                                        $icon_class = 'bx bx-x';
                                        if ($status == 'tersedia') {
                                            $badge_class = 'bg-label-success';
                                            $icon_class = 'bx bx-check';
                                        } elseif ($status == 'dalam_perbaikan') {
                                            $badge_class = 'bg-label-warning';
                                            $icon_class = 'bx bx-wrench';
                                        } elseif ($status == 'tidak_tersedia') {
                                            $badge_class = 'bg-label-danger';
                                            $icon_class = 'bx bx-block';
                                        }
                                        ?>
                                        <span class="badge <?= $badge_class; ?>">
                                            <i class="tf-icon <?= $icon_class; ?>"></i>
                                            <?= str_replace('_', ' ', ucwords($status)); ?>
                                        </span>
                                    </td>

                                    <td class="aksi-cell">
                                        <div class="d-flex flex-nowrap">
                                            <a class="btn btn-warning btn-sm me-1" href="form_lab.php?id=<?= $data['id_lab']; ?>">
                                                <i class="bx bx-edit-alt me-1"></i> Edit
                                            </a>

                                            <a class="btn btn-danger btn-sm btn-delete"
                                                href="proses_ruangan.php?aksi=hapus&id=<?= $data['id_lab']; ?>">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                            } // Akhir while
                        } else {
                            ?>
                            <tr>
                                <td colspan="9" class="text-center">Belum ada data laboratorium.</td>
                            </tr>
                        <?php
                        } // Akhir if-else
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // 1. Ambil semua tombol dengan class 'btn-delete'
    const deleteButtons = document.querySelectorAll('.btn-delete');

    // 2. Loop semua tombol yang ditemukan
    deleteButtons.forEach(button => {

        // 3. Tambahkan event listener 'click' untuk setiap tombol
        button.addEventListener('click', function(event) {

            // 4. Hentikan aksi default dari link
            event.preventDefault();

            // 5. Ambil URL hapus dari atribut 'href'
            const deleteUrl = this.href;

            // 6. Tampilkan konfirmasi SweetAlert
            Swal.fire({
                title: 'Apakah Anda yakin menghapus laboratorium ini?',
                text: "Data laboratorium yang sudah dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                // 7. Jika pengguna menekan "Ya, hapus!"
                if (result.isConfirmed) {
                    // Arahkan browser ke URL hapus
                    window.location.href = deleteUrl;
                }
            });
        });
    });
</script>


<?php
// Logika SweetAlert
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    $tipe_alert = ($pesan['tipe'] == 'success') ? 'success' : 'error';
    $judul_alert = ($pesan['tipe'] == 'success') ? 'Berhasil!' : 'Gagal!';

    echo "
<script>
    Swal.fire({
        title: '" . addslashes($judul_alert) . "',
        text: '" . addslashes($pesan['isi']) . "',
        icon: '" . $tipe_alert . "',
        confirmButtonText: 'OK'
    });
</script>
";

    // Hapus session 'pesan' setelah ditampilkan
    unset($_SESSION['pesan']);
}

require '../../../partials/mahasiswa/footer.php';
?>