<?php
$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';



$sql = "
    SELECT 
        p.id_peminjaman,
        l.nama_lab,
        m.nama AS nama_mahasiswa,
        p.tanggal_pinjam,
        p.jam_mulai,
        p.jam_selesai,
        p.keperluan,
        p.jumlah_peserta,
        p.created_at
    FROM peminjaman p
    INNER JOIN laboratorium l ON p.lab_id = l.id_lab
    INNER JOIN mahasiswa m ON p.mahasiswa_id = m.id_mahasiswa
    WHERE p.status = 'menunggu' AND p.lab_id IS NOT NULL
    ORDER BY p.created_at ASC
";

$result = mysqli_query($koneksi, $sql);

// Cek notifikasi
if (isset($_SESSION['alert'])):
    $alert = $_SESSION['alert'];
    unset($_SESSION['alert']);
?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
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
    <h2 class="fw-bold mb-4 text-primary">✅ Persetujuan Peminjaman Lab</h2>
    <p class="text-muted">Daftar permintaan peminjaman lab yang memerlukan verifikasi dan persetujuan.</p>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="dataTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lab</th>
                            <th>Peminjam</th>
                            <th>Tanggal & Waktu</th>
                            <th>Keperluan</th>
                            <th>Peserta</th>
                            <th>Diajukan Pada</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= $row['id_peminjaman'] ?></td>
                                <td class="fw-semibold text-primary"><?= htmlspecialchars($row['nama_lab']) ?></td>
                                <td><?= htmlspecialchars($row['nama_mahasiswa']) ?></td>
                                <td>
                                    <?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?><br>
                                    <span class="badge bg-secondary">
                                        <?= substr($row['jam_mulai'], 0, 5) ?> - <?= substr($row['jam_selesai'], 0, 5) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['keperluan']) ?></td>
                                <td><?= (int)$row['jumlah_peserta'] ?></td>
                                <td><?= date('d/m/y H:i', strtotime($row['created_at'])) ?></td>
                                <td>
                                    <button class="btn btn-success btn-sm action-btn mb-1"
                                        data-id="<?= $row['id_peminjaman'] ?>"
                                        data-action="disetujui"
                                        data-entity="<?= htmlspecialchars($row['nama_lab']) ?>">
                                        <i class="bi bi-check-lg"></i> Setuju
                                    </button>

                                    <button class="btn btn-danger btn-sm action-btn"
                                        data-id="<?= $row['id_peminjaman'] ?>"
                                        data-action="ditolak"
                                        data-entity="<?= htmlspecialchars($row['nama_lab']) ?>">
                                        <i class="bi bi-x-lg"></i> Tolak
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-info py-4">
                                    <i class="bi bi-info-circle-fill"></i>
                                    Tidak ada permintaan peminjaman lab yang menunggu saat ini.
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
document.addEventListener('DOMContentLoaded', () => {
    const actionButtons = document.querySelectorAll('.action-btn');
    const prosesUrl = '../../../pengelola_ruangan/persetujuan/proses_persetujuan.php';

    actionButtons.forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const action = this.dataset.action;
            const entityName = this.dataset.entity;

            Swal.fire({
                title: action === 'disetujui' ? 'Setujui Peminjaman?' : 'Tolak Peminjaman?',
                html: action === 'disetujui' 
                    ? `Anda akan <strong>MENYETUJUI</strong> peminjaman lab <strong>${entityName}</strong>.`
                    : `Anda akan <strong>MENOLAK</strong> peminjaman lab <strong>${entityName}</strong>.`,
                icon: action === 'disetujui' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonColor: action === 'disetujui' ? '#28a745' : '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: action === 'disetujui' ? 'Ya, Setujui' : 'Ya, Tolak',
                cancelButtonText: 'Batal'
            }).then(result => {
                if (result.isConfirmed) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = prosesUrl;

                    form.innerHTML = `
                        <input type="hidden" name="id_peminjaman" value="${id}">
                        <input type="hidden" name="action" value="${action}">
                    `;

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
});
</script>

<?php
require '../../../partials/mahasiswa/footer.php';
mysqli_close($koneksi);
?>
