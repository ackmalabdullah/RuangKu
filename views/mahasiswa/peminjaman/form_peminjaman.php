<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Pastikan koneksi tersedia dan berhasil
if (!isset($koneksi) || $koneksi->connect_error) {
    die("<div class='alert alert-danger'>Koneksi database gagal atau tidak ditemukan.</div>");
}

// --- 1. Ambil Parameter dari URL dan Sanitasi ---
$tipe_entitas = isset($_GET['tipe']) ? strtolower(trim($_GET['tipe'])) : ''; // Sanitasi tipe
$entitas_id = isset($_GET['id_entitas']) ? (int)$_GET['id_entitas'] : 0;
$tanggal_pinjam = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

// Redirect jika data tidak lengkap atau tipe tidak valid
if ($entitas_id == 0 || empty($tanggal_pinjam) || !in_array($tipe_entitas, ['ruangan', 'laboratorium'])) {
    header('Location: peminjaman.php');
    exit();
}

// --- 2. Tentukan Variabel Berdasarkan Tipe Entitas ---
if ($tipe_entitas == 'ruangan') {
    $tabel_entitas = 'ruangan';
    $kolom_id = 'id_ruangan';
    $kolom_nama = 'nama_ruangan';
    $nama_entitas_kapital = 'Ruangan';
} else { // laboratorium
    $tabel_entitas = 'laboratorium';
    $kolom_id = 'id_lab';
    $kolom_nama = 'nama_lab';
    $nama_entitas_kapital = 'Laboratorium';
}

// --- 3. Ambil Nama Entitas (Ruangan atau Lab) menggunakan Prepared Statement ---
// Perbaikan: Menggunakan Prepared Statement untuk keamanan (mencegah SQL Injection pada $entitas_id)
$nama_entitas = 'Tidak diketahui';
$sql_nama_entitas = "SELECT {$kolom_nama} FROM {$tabel_entitas} WHERE {$kolom_id} = ?";
$stmt = mysqli_prepare($koneksi, $sql_nama_entitas);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, 'i', $entitas_id);
    mysqli_stmt_execute($stmt);
    $result_nama = mysqli_stmt_get_result($stmt);

    if ($result_nama && mysqli_num_rows($result_nama) > 0) {
        $row = mysqli_fetch_assoc($result_nama);
        $nama_entitas = $row[$kolom_nama];
    }
    mysqli_stmt_close($stmt);
} else {
    // Log error jika prepared statement gagal
    error_log("Gagal membuat prepared statement untuk ambil nama entitas: " . mysqli_error($koneksi));
}

// Format tanggal untuk tampilan menggunakan IntlDateFormatter
// Perbaikan: Pengecekan class sebelum inisialisasi
if (class_exists('IntlDateFormatter')) {
    try {
        $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE);
        $tanggal_terformat = $formatter->format(new DateTime($tanggal_pinjam));
    } catch (Exception $e) {
        // Fallback jika formatting tanggal gagal
        $tanggal_terformat = date('d F Y', strtotime($tanggal_pinjam));
        error_log("IntlDateFormatter gagal: " . $e->getMessage());
    }
} else {
    // Fallback jika Intl extension tidak aktif
    $tanggal_terformat = date('d F Y', strtotime($tanggal_pinjam));
    echo "<script>console.warn('Ekstensi PHP intl tidak diaktifkan. Tanggal ditampilkan dengan format standar.');</script>";
}

?>

<div class="container my-5">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-md-5">
            <h3 class="mb-4 text-danger fw-bold">📝 Form Peminjaman <?= htmlspecialchars($nama_entitas_kapital) ?></h3>

            <div class="alert alert-warning border-0 shadow-sm">
                Anda akan meminjam **<?= htmlspecialchars($nama_entitas) ?>** pada tanggal
                <strong class="text-danger"><?= htmlspecialchars($tanggal_terformat) ?></strong>.
            </div>

            <form id="formPeminjaman" action="proses_peminjaman.php" method="POST" class="needs-validation" novalidate>

                <input type="hidden" name="tipe_entitas" value="<?= htmlspecialchars($tipe_entitas) ?>">
                <input type="hidden" name="entitas_id" value="<?= htmlspecialchars($entitas_id) ?>">
                <input type="hidden" name="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>">

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jam_mulai" class="form-label">Jam Mulai</label>
                            <input type="time" class="form-control" id="jam_mulai" name="jam_mulai" required>
                            <div class="invalid-feedback">Jam mulai harus diisi.</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="jam_selesai" class="form-label">Jam Selesai</label>
                            <input type="time" class="form-control" id="jam_selesai" name="jam_selesai" required>
                            <div class="invalid-feedback">Jam selesai harus diisi.</div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="keperluan" class="form-label">Keperluan (Kegiatan)</label>
                    <textarea class="form-control" id="keperluan" name="keperluan" rows="3" placeholder="Contoh: Diskusi kelompok matakuliah Algoritma Pemrograman" required></textarea>
                    <div class="invalid-feedback">Keperluan harus diisi.</div>
                </div>

                <div class="mb-4">
                    <label for="jumlah_peserta" class="form-label">Jumlah Peserta</label>
                    <input type="number" class="form-control" id="jumlah_peserta" name="jumlah_peserta" placeholder="Masukkan jumlah peserta" min="1" required>
                    <div class="invalid-feedback">Jumlah peserta harus diisi dan minimal 1.</div>
                </div>

                <div class="d-flex justify-content-between pt-3 border-top">
                    <a href="peminjaman.php" class="btn btn-outline-secondary">
                        ← Kembali ke Pilihan
                    </a>
                    <button type="submit" class="btn btn-danger fw-bold">
                        <i class="bi bi-calendar-check"></i> Ajukan Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Validasi form Bootstrap dan Konfirmasi SweetAlert
    (() => {
        'use strict'
        const form = document.getElementById('formPeminjaman');
        form.addEventListener('submit', function(event) {
            // Periksa validitas input bawaan HTML
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            } else {
                // Lakukan validasi jam_mulai vs jam_selesai di sisi klien
                const jamMulai = document.getElementById('jam_mulai').value;
                const jamSelesai = document.getElementById('jam_selesai').value;
                
                if (jamMulai === "" || jamSelesai === "") {
                    // Biarkan validasi HTML native yang menangani jika kosong
                } else if (jamMulai >= jamSelesai) {
                    event.preventDefault();
                    event.stopPropagation();
                    // Tampilkan pesan error custom
                    Swal.fire({
                        icon: 'warning',
                        title: 'Waktu Tidak Valid',
                        text: 'Jam selesai harus lebih besar dari jam mulai.',
                    });
                    
                    // Tambahkan class invalid pada input jam_selesai
                    document.getElementById('jam_selesai').classList.add('is-invalid');
                    document.getElementById('jam_selesai').focus();

                } else {
                    // Jika valid, tampilkan konfirmasi SweetAlert
                    event.preventDefault();
                    Swal.fire({
                        title: 'Konfirmasi Peminjaman',
                        text: 'Apakah Anda yakin ingin mengajukan peminjaman ini? Data akan dikirim untuk diverifikasi.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, ajukan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#dc3545', // Warna Merah (danger)
                        cancelButtonColor: '#6c757d' // Warna Abu-abu (secondary)
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                }
            }
            form.classList.add('was-validated')
        }, false)
    })();
</script>

<?php
// Perbaikan: Hapus pengecekan class IntlDateFormatter di sini, sudah dilakukan di bagian atas.
require '../../../partials/mahasiswa/footer.php';
?>