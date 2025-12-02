<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Pastikan koneksi tersedia dan berhasil
if (!isset($koneksi) || $koneksi->connect_error) {
    die("<div class='alert alert-danger text-center'>Koneksi database gagal atau tidak ditemukan. Hubungi administrator.</div>");
}

// ================================================
// 1. Ambil dan sanitasi parameter dari URL
// ================================================
$tipe_entitas   = isset($_GET['tipe']) ? strtolower(trim($_GET['tipe'])) : '';
$entitas_id     = isset($_GET['id_entitas']) ? (int)$_GET['id_entitas'] : 0;
$tanggal_pinjam = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

// Validasi parameter wajib
if ($entitas_id == 0 || empty($tanggal_pinjam) || !in_array($tipe_entitas, ['ruangan', 'laboratorium'])) {
    $_SESSION['alert'] = [
        'icon'  => 'error',
        'title' => 'Akses Ditolak',
        'text'  => 'Parameter tidak lengkap atau tidak valid.'
    ];
    header('Location: peminjaman.php');
    exit();
}

// ================================================
// 2. Tentukan tabel, kolom, dan action URL berdasarkan tipe
// ================================================
if ($tipe_entitas === 'ruangan') {
    $tabel_entitas        = 'ruangan';
    $kolom_id             = 'id_ruangan';
    $kolom_nama           = 'nama_ruangan';
    $nama_entitas_kapital = 'Ruangan';
    $action_proses        = 'proses_peminjaman_ruangan.php'; // KHUSUS RUANGAN
} else {
    $tabel_entitas        = 'laboratorium';
    $kolom_id             = 'id_lab';
    $kolom_nama           = 'nama_lab';
    $nama_entitas_kapital = 'Laboratorium';
    $action_proses        = 'proses_peminjaman_lab.php';     // KHUSUS LAB
}

// ================================================
// 3. Ambil nama entitas (ruangan/lab) dengan prepared statement
// ================================================
$nama_entitas = 'Tidak diketahui';
$error_nama   = false;

$sql_nama = "SELECT {$kolom_nama} FROM {$tabel_entitas} WHERE {$kolom_id} = ? LIMIT 1";
if ($stmt = mysqli_prepare($koneksi, $sql_nama)) {
    mysqli_stmt_bind_param($stmt, 'i', $entitas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nama_entitas = $row[$kolom_nama];
    } else {
        $error_nama = true;
    }
    mysqli_stmt_close($stmt);
} else {
    error_log("Prepared statement gagal (ambil nama): " . mysqli_error($koneksi));
    $error_nama = true;
}

if ($error_nama) {
    $_SESSION['alert'] = [
        'icon'  => 'error',
        'title' => 'Data Tidak Ditemukan',
        'text'  => 'Ruangan atau laboratorium tidak ditemukan di database.'
    ];
    header('Location: peminjaman.php');
    exit();
}

// ================================================
// 4. Format tanggal Indonesia yang cantik
// ================================================
if (class_exists('IntlDateFormatter')) {
    try {
        $formatter = new IntlDateFormatter(
            'id_ID',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'Asia/Jakarta',
            IntlDateFormatter::GREGORIAN
        );
        $tanggal_terformat = $formatter->format(new DateTime($tanggal_pinjam));
    } catch (Exception $e) {
        $tanggal_terformat = date('l, d F Y', strtotime($tanggal_pinjam));
    }
} else {
    $tanggal_terformat = date('l, d F Y', strtotime($tanggal_pinjam));
}
?>

<div class="container my-5">
    <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
        <div class="card-header bg-danger text-white text-center py-4">
            <h2 class="mb-0 fw-bold">Form Pengajuan Peminjaman <?= htmlspecialchars($nama_entitas_kapital) ?></h2>
        </div>
        <div class="card-body p-4 p-md-5">

            <!-- Info Peminjaman -->
            <div class="alert alert-info border-0 shadow-sm mb-4">
                <h5 class="mb-2">Informasi Peminjaman:</h5>
                <p class="mb-0">
                    Anda akan mengajukan peminjaman <strong class="text-danger"><?= htmlspecialchars($nama_entitas) ?></strong><br>
                    pada hari <strong class="text-primary"><?= htmlspecialchars($tanggal_terformat) ?></strong>.
                </p>
            </div>

            <!-- Form Peminjaman -->
            <form id="formPeminjaman" action="<?= htmlspecialchars($action_proses) ?>" method="POST" class="needs-validation" novalidate>

                <!-- Hidden Inputs -->
                <input type="hidden" name="tipe_entitas" value="<?= htmlspecialchars($tipe_entitas) ?>">
                <input type="hidden" name="entitas_id" value="<?= $entitas_id ?>">
                <input type="hidden" name="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>">

                <!-- Jam Mulai & Selesai -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label for="jam_mulai" class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" class="form-control form-control-lg" id="jam_mulai" name="jam_mulai" required>
                        <div class="invalid-feedback">Jam mulai wajib diisi.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="jam_selesai" class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" class="form-control form-control-lg" id="jam_selesai" name="jam_selesai" required>
                        <div class="invalid-feedback">Jam selesai wajib diisi dan harus lebih besar dari jam mulai.</div>
                    </div>
                </div>

                <!-- Keperluan -->
                <div class="mb-4">
                    <label for="keperluan" class="form-label fw-semibold">Keperluan / Kegiatan <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="keperluan" name="keperluan" rows="4" 
                              placeholder="Contoh: Praktikum Pemrograman Web, Diskusi Tugas Akhir, dll" required></textarea>
                    <div class="invalid-feedback">Keperluan wajib diisi dengan jelas.</div>
                </div>

                <!-- Jumlah Peserta -->
                <div class="mb-5">
                    <label for="jumlah_peserta" class="form-label fw-semibold">Jumlah Peserta <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-lg" id="jumlah_peserta" name="jumlah_peserta" 
                               min="1" max="100" placeholder="Masukkan jumlah peserta" required>
                    <div class="invalid-feedback">Jumlah peserta minimal 1 orang.</div>
                </div>

                <!-- Tombol -->
                <div class="d-flex flex-column flex-md-row justify-content-between gap-3 pt-4 border-top">
                    <a href="peminjaman.php" class="btn btn-outline-secondary btn-lg px-5">
                        Kembali
                    </a>
                    <button type="submit" class="btn btn-danger btn-lg px-5 fw-bold shadow-sm">
                        Ajukan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SweetAlert2 + Validasi JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    (() => {
        'use strict'
        const form = document.getElementById('formPeminjaman');

        form.addEventListener('submit', function(event) {
            // Reset invalid state
            document.getElementById('jam_selesai').classList.remove('is-invalid');

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                const jamMulai   = document.getElementById('jam_mulai').value;
                const jamSelesai = document.getElementById('jam_selesai').value;

                if (jamMulai >= jamSelesai) {
                    event.preventDefault();
                    document.getElementById('jam_selesai').classList.add('is-invalid');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Waktu Tidak Valid!',
                        text: 'Jam selesai harus lebih besar dari jam mulai ya sayang',
                        confirmButtonColor: '#dc3545'
                    });
                    return false;
                }

                // Konfirmasi sebelum kirim
                event.preventDefault();
                Swal.fire({
                    title: 'Yakin Mau Ajukan?',
                    html: `Kamu akan mengajukan peminjaman <b><?= htmlspecialchars($nama_entitas) ?></b> pada <b><?= htmlspecialchars($tanggal_terformat) ?></b>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Ajukan Sekarang!',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            }

            form.classList.add('was-validated');
        }, false);
    })();
</script>

<?php require '../../../partials/mahasiswa/footer.php'; ?>