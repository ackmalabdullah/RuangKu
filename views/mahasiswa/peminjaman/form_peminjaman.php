<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

if (!isset($koneksi) || $koneksi->connect_error) {
    die("<div class='alert alert-danger text-center'>Koneksi database gagal. Hubungi administrator.</div>");
}

$tipe_entitas   = isset($_GET['tipe']) ? strtolower(trim($_GET['tipe'])) : '';
$entitas_id     = isset($_GET['id_entitas']) ? (int)$_GET['id_entitas'] : 0;
$tanggal_pinjam = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : '';

if ($entitas_id == 0 || empty($tanggal_pinjam) || !in_array($tipe_entitas, ['ruangan', 'laboratorium'])) {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Akses Ditolak','text'=>'Parameter tidak valid.'];
    header('Location: peminjaman.php'); exit();
}

if ($tipe_entitas === 'ruangan') {
    $tabel_entitas = 'ruangan'; $kolom_id = 'id_ruangan'; $kolom_nama = 'nama_ruangan';
    $nama_entitas_kapital = 'Ruangan'; $action_proses = 'proses_peminjaman_ruangan.php';
} else {
    $tabel_entitas = 'laboratorium'; $kolom_id = 'id_lab'; $kolom_nama = 'nama_lab';
    $nama_entitas_kapital = 'Laboratorium'; $action_proses = 'proses_peminjaman_lab.php';
}

$nama_entitas = 'Tidak diketahui';
$sql = "SELECT {$kolom_nama} FROM {$tabel_entitas} WHERE {$kolom_id} = ? LIMIT 1";
if ($stmt = mysqli_prepare($koneksi, $sql)) {
    mysqli_stmt_bind_param($stmt, 'i', $entitas_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) $nama_entitas = $row[$kolom_nama];
    mysqli_stmt_close($stmt);
}
if ($nama_entitas === 'Tidak diketahui') {
    $_SESSION['alert'] = ['icon'=>'error','title'=>'Tidak Ditemukan','text'=>'Ruangan/laboratorium tidak ada.'];
    header('Location: peminjaman.php'); exit();
}

if (class_exists('IntlDateFormatter')) {
    $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Asia/Jakarta');
    $tanggal_terformat = $formatter->format(new DateTime($tanggal_pinjam));
} else {
    $tanggal_terformat = date('l, d F Y', strtotime($tanggal_pinjam));
}
?>

<div class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xxl-9">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header text-white text-center py-5" style="background: linear-gradient(135deg, #5e72e4, #825ee4);">
                    <h2 class="mb-2 fw-bold fs-1">Pengajuan Peminjaman</h2>
                    <p class="mb-0 fs-5 opacity-90"><?= htmlspecialchars($nama_entitas_kapital) ?></p>
                </div>

                <div class="card-body p-4 p-md-5">
                    <div class="alert border-0 rounded-4 mb-5" style="background:#e3f2fd;color:#1565c0;border-left:6px solid #5e72e4;">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-info-circle fa-2x"></i>
                            <div>
                                <strong class="fs-5"><?= htmlspecialchars($nama_entitas) ?></strong><br>
                                <i class="far fa-calendar-alt me-2"></i><strong><?= htmlspecialchars($tanggal_terformat) ?></strong>
                            </div>
                        </div>
                    </div>

                    <form id="formPeminjaman" action="<?= htmlspecialchars($action_proses) ?>" method="POST">
                        <input type="hidden" name="tipe_entitas" value="<?= htmlspecialchars($tipe_entitas) ?>">
                        <input type="hidden" name="entitas_id" value="<?= $entitas_id ?>">
                        <input type="hidden" name="tanggal_pinjam" value="<?= htmlspecialchars($tanggal_pinjam) ?>">

                        <!-- JAM CUSTOM CANTIK -->
                        <div class="mb-5">
                            <label class="form-label fw-bold text-primary mb-4">
                                Waktu Peminjaman <span class="text-danger">*</span>
                            </label>

                            <div class="row g-4 align-items-center justify-content-center">
                                <!-- MULAI -->
                                <div class="col-12 col-md-5">
                                    <div class="jam-custom mulai" data-default-h="08" data-default-m="00">
                                        <div class="jam-label">Mulai</div>
                                        <div class="jam-display">08 : 00</div>
                                        <div class="jam-dropdown">
                                            <div class="jam-scroll">
                                                <?php for($h=0;$h<=23;$h++): ?>
                                                    <div class="jam-item" data-value="<?= str_pad($h,2,'0',STR_PAD_LEFT) ?>" <?= $h==8?'data-active':'' ?>>
                                                        <?= str_pad($h,2,'0',STR_PAD_LEFT) ?>
                                                    </div>
                                                <?php endfor; ?>
                                                <div class="jam-item" data-value="24">24</div>
                                            </div>
                                            <div class="menit-scroll">
                                                <?php for($m=0;$m<=59;$m++): ?>
                                                    <div class="jam-item" data-value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= $m==0?'data-active':'' ?>>
                                                        <?= str_pad($m,2,'0',STR_PAD_LEFT) ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2 text-center my-3 my-md-0">
                                    <span class="text-muted fw-light fs-2">→</span>
                                </div>

                                <!-- SELESAI -->
                                <div class="col-12 col-md-5">
                                    <div class="jam-custom selesai" data-default-h="10" data-default-m="00">
                                        <div class="jam-label">Selesai</div>
                                        <div class="jam-display">10 : 00</div>
                                        <div class="jam-dropdown">
                                            <div class="jam-scroll">
                                                <?php for($h=0;$h<=23;$h++): ?>
                                                    <div class="jam-item" data-value="<?= str_pad($h,2,'0',STR_PAD_LEFT) ?>" <?= $h==10?'data-active':'' ?>>
                                                        <?= str_pad($h,2,'0',STR_PAD_LEFT) ?>
                                                    </div>
                                                <?php endfor; ?>
                                                <div class="jam-item" data-value="24">24</div>
                                            </div>
                                            <div class="menit-scroll">
                                                <?php for($m=0;$m<=59;$m++): ?>
                                                    <div class="jam-item" data-value="<?= str_pad($m,2,'0',STR_PAD_LEFT) ?>" <?= $m==0?'data-active':'' ?>>
                                                        <?= str_pad($m,2,'0',STR_PAD_LEFT) ?>
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="jam_mulai" id="final_jam_mulai" value="08:00">
                            <input type="hidden" name="jam_selesai" id="final_jam_selesai" value="10:00">
                        </div>

                        <!-- Keperluan -->
                        <div class="mb-4">
                            <label for="keperluan" class="form-label fw-bold text-dark">
                                Keperluan / Kegiatan <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control rounded-3" id="keperluan" name="keperluan" rows="5" placeholder="Contoh: Praktikum, Rapat, dll" style="resize:none;" required></textarea>
                            <div class="invalid-feedback">Keperluan wajib diisi</div>
                        </div>

                        <!-- Jumlah Peserta -->
                        <div class="mb-5">
                            <label for="jumlah_peserta" class="form-label fw-bold text-dark">
                                Jumlah Peserta <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control form-control-lg rounded-pill" id="jumlah_peserta" name="jumlah_peserta" min="1" max="300" placeholder="Contoh: 30" required>
                            <div class="invalid-feedback">Minimal 1 orang</div>
                        </div>

                        <div class="d-grid d-md-flex justify-content-between gap-3 mt-5">
                            <a href="peminjaman.php" class="btn btn-outline-secondary btn-lg rounded-pill px-5">Kembali</a>
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow" style="background:linear-gradient(135deg,#5e72e4,#825ee4);border:none;">
                                Ajukan Peminjaman
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .jam-custom{position:relative;background:#f8fbff;border:2px solid #e0e0e0;border-radius:50rem;padding:16px 20px;transition:all .3s;cursor:pointer;user-select:none}
    .jam-custom.active,.jam-custom:focus-within{border-color:#5e72e4;box-shadow:0 0 0 6px rgba(94,114,228,.15)}
    .jam-label{position:absolute;top:-10px;left:24px;background:#f8fbff;padding:0 12px;font-weight:600;color:#5e72e4;font-size:.9rem}
    .jam-display{text-align:center;font-size:2rem;font-weight:700;color:#2c3e50;letter-spacing:4px;padding:8px 0;transition:all .2s}
    .jam-display:hover{background:rgba(94,114,228,.05)}
    .jam-dropdown{position:absolute;top:100%;left:0;right:0;background:white;border:2px solid #5e72e4;border-radius:1rem;margin-top:8px;box-shadow:0 10px 30px rgba(0,0,0,.15);display:none;z-index:999;overflow:hidden}
    .jam-custom.active .jam-dropdown{display:flex}
    .jam-scroll,.menit-scroll{flex:1;max-height:240px;overflow-y:auto;padding:8px 0}
    .jam-scroll::-webkit-scrollbar,.menit-scroll::-webkit-scrollbar{width:6px}
    .jam-scroll::-webkit-scrollbar-track,.menit-scroll::-webkit-scrollbar-track{background:#f1f1f1;border-radius:10px}
    .jam-scroll::-webkit-scrollbar-thumb,.menit-scroll::-webkit-scrollbar-thumb{background:#c1c1c1;border-radius:10px}
    .jam-item{padding:12px;text-align:center;font-size:1.3rem;font-weight:600;color:#666;transition:all .2s;cursor:pointer}
    .jam-item:hover,.jam-item[data-active]{background:#5e72e4;color:white}
    @media (max-width:576px){.jam-display{font-size:1.6rem;letter-spacing:2px}.jam-item{font-size:1.2rem;padding:10px}}
    .btn-primary{background:linear-gradient(135deg,#5e72e4,#825ee4)!important;border:none!important}
    .btn-primary:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(94,114,228,.4)!important}
    .form-control:focus,.form-select:focus{border-color:#5e72e4!important;box-shadow:0 0 0 .25rem rgba(94,114,228,.25)!important}
    .text-primary{color:#5e72e4!important}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // INIT JAM CUSTOM
    document.querySelectorAll('.jam-custom').forEach(container => {
        const display = container.querySelector('.jam-display');
        const dropdown = container.querySelector('.jam-dropdown');
        const jamScroll = container.querySelector('.jam-scroll');
        const menitScroll = container.querySelector('.menit-scroll');
        const isMulai = container.classList.contains('mulai');

        // Buka dropdown saat klik display
        display.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelectorAll('.jam-custom').forEach(c => c.classList.remove('active'));
            container.classList.add('active');
        });

        // Tutup saat klik luar
        document.addEventListener('click', () => container.classList.remove('active'));
        dropdown.addEventListener('click', e => e.stopPropagation());

        // Pilih jam/menit
        [jamScroll, menitScroll].forEach(scroll => {
            scroll.querySelectorAll('.jam-item').forEach(item => {
                item.addEventListener('click', function() {
                    scroll.querySelectorAll('.jam-item').forEach(i => i.removeAttribute('data-active'));
                    this.setAttribute('data-active', '');
                    update();
                });
            });
        });

        function update() {
            const h = jamScroll.querySelector('[data-active]')?.dataset.value || container.dataset.defaultH;
            const m = menitScroll.querySelector('[data-active]')?.dataset.value || container.dataset.defaultM;
            display.textContent = h + ' : ' + m;

            const value = (h === '24' && !isMulai) ? '24:00' : h + ':' + m;
            if (isMulai) {
                document.getElementById('final_jam_mulai').value = h + ':' + m;
            } else {
                document.getElementById('final_jam_selesai').value = value;
            }
        }

        // Init
        update();
    });

    // SUBMIT + VALIDASI
    document.getElementById('formPeminjaman').addEventListener('submit', function(e) {
        e.preventDefault();

        // Reset
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.jam-custom').forEach(el => el.style.cssText = '');

        let error = '';
        const keperluan = document.getElementById('keperluan').value.trim();
        const peserta = document.getElementById('jumlah_peserta').value;

        if (!keperluan) {
            error += '• Kolom Keperluan / Kegiatan wajib diisi<br>';
            document.getElementById('keperluan').classList.add('is-invalid');
        }
        if (!peserta || peserta < 1) {
            error += '• Jumlah peserta minimal 1 orang<br>';
            document.getElementById('jumlah_peserta').classList.add('is-invalid');
        }

        // Ambil jam dari custom
        const jamMulai = document.getElementById('final_jam_mulai').value;
        const jamSelesai = document.getElementById('final_jam_selesai').value;

        if (jamMulai >= jamSelesai && jamSelesai !== '24:00') {
            error += '• Jam selesai harus lebih besar dari jam mulai<br>';
            document.querySelector('.jam-custom.selesai').style.borderColor = '#dc3545';
        }

        if (error) {
            Swal.fire({
                icon: 'warning',
                title: 'Data Belum Lengkap',
                html: '<div style="text-align:left">' + error + '</div>',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#5e72e4'
            });
            return;
        }

        Swal.fire({
            title: 'Ajukan Peminjaman?',
            html: `<strong class="text-primary">${'<?= htmlspecialchars($nama_entitas) ?>'}</strong><br>
                   <strong>${'<?= htmlspecialchars($tanggal_terformat) ?>'}</strong><br>
                   <small class="text-success">${jamMulai} → ${jamSelesai}</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Ajukan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#5e72e4',
            reverseButtons: true
        }).then(result => {
            if (result.isConfirmed) this.submit();
        });
    });
});
</script>

<?php require '../../../partials/mahasiswa/footer.php'; ?>