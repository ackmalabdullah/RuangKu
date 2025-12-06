<?php
// FILE: views/pengelola_ruangan/persetujuan/persetujuan.php

$required_role = 'pengelola_ruangan';

require '../../../partials/mahasiswa/header.php'; 
require '../../../partials/mahasiswa/sidebar.php'; 
require '../../../partials/mahasiswa/navbar.php'; 

// Pastikan koneksi sudah ada
if (!isset($koneksi)) {
    require '../../../settings/koneksi.php';
    $db = new Database();
    $koneksi = $db->conn;
}

// >>> KOREKSI 2: QUERY SQL MENAMBAHKAN status_jurusan = 'disetujui' <<<
$sql = "
    SELECT 
        p.id_peminjaman,
        r.nama_ruangan,
        m.nama AS nama_mahasiswa,
        p.tanggal_pinjam,
        p.jam_mulai,
        p.jam_selesai,
        p.keperluan,
        p.jumlah_peserta,
        p.created_at
    FROM peminjaman p
    INNER JOIN ruangan r ON p.ruangan_id = r.id_ruangan
    INNER JOIN mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa
    -- FILTER UTAMA PENGELOLA RUANGAN: Sudah disetujui Jurusan DAN menunggu persetujuan final
    WHERE p.status = 'menunggu' 
      AND p.ruangan_id IS NOT NULL
      AND p.status_jurusan = 'disetujui' 
    ORDER BY p.created_at ASC
";

$result = mysqli_query($koneksi, $sql);

// Cek notifikasi dari proses_persetujuan.php (SweetAlert)
if (isset($_SESSION['alert'])): 
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '<?= $alert['icon'] ?>',
            title: '<?= $alert['title'] ?>',
            html: '<?= $alert['text'] ?>',
            confirmButtonText: 'OK'
        });
    });
</script>
<?php endif; ?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-primary">✅ Persetujuan Peminjaman Ruangan </h2>
    <p class="text-muted">Daftar permintaan peminjaman ruangan yang sudah diverifikasi Jurusan dan memerlukan persetujuan jadwal.</p>
    
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Ruangan</th>
                            <th>Peminjam</th>
                            <th>Tanggal & Waktu</th>
                            <th>Keperluan</th>
                            <th>Peserta</th>
                            <th>Diajukan Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['id_peminjaman'] ?></td>
                                <td class="fw-semibold text-danger"><?= htmlspecialchars($row['nama_ruangan']) ?></td>
                                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?><br>
                                    <span class="badge bg-secondary"><?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?></span>
                                </td>
                                <td><?= htmlspecialchars($row['keperluan']) ?></td>
                                <td><?= $row['jumlah_peserta'] ?></td>
                                <td><?= date('d/m/y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm mb-1 action-btn" 
                                        data-id="<?= $row['id_peminjaman'] ?>" 
                                        data-action="disetujui" 
                                        data-entity="<?= htmlspecialchars($row['nama_ruangan']) ?>">
                                        <i class="bi bi-check-lg"></i> Setuju
                                    </button>
                                    <button class="btn btn-danger btn-sm action-btn" 
                                        data-id="<?= $row['id_peminjaman'] ?>" 
                                        data-action="ditolak" 
                                        data-entity="<?= htmlspecialchars($row['nama_ruangan']) ?>">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-info py-4">
                                    <i class="bi bi-info-circle-fill"></i> Tidak ada permintaan peminjaman ruangan yang menunggu persetujuan akhir saat ini.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const actionButtons = document.querySelectorAll('.action-btn');
        const prosesUrl = 'proses_persetujuan.php'; 

        actionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const action = this.dataset.action;
                const entityName = this.dataset.entity; // Nama Ruangan
                
                const title = action === 'disetujui' ? 'Setujui Peminjaman?' : 'Tolak Peminjaman?';
                const text = action === 'disetujui' 
                    ? `Anda akan **MENYETUJUI** peminjaman ruangan **${entityName}**. Slot waktu akan dikunci dan peminjaman dianggap **SAH**.`
                    : `Anda akan **MENOLAK** peminjaman ruangan **${entityName}**. Proses akan dibatalkan.`;
                const confirmButtonColor = action === 'disetujui' ? '#28a745' : '#dc3545';
                const confirmButtonText = action === 'disetujui' ? 'Ya, Setujui' : 'Ya, Tolak';

                Swal.fire({
                    title: title,
                    html: text,
                    icon: action === 'disetujui' ? 'question' : 'warning',
                    showCancelButton: true,
                    confirmButtonColor: confirmButtonColor,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmButtonText,
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim data menggunakan form tersembunyi
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = prosesUrl;

                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'id_peminjaman';
                        idInput.value = id;

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = action; 

                        form.appendChild(idInput);
                        form.appendChild(actionInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            });
        });
    });
</script>

<?php
// >>> KOREKSI 3: Ganti partials dari 'mahasiswa' ke 'pengelola_ruangan' (sesuaikan path Anda) <<<
require '../../../partials/mahasiswa/footer.php';
mysqli_close($koneksi);
?>