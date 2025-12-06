<?php
// Role yang diperlukan adalah 'pengelola_ruangan'
$required_role = 'pengelola_ruangan';

// Memuat komponen layout (Pastikan file-file ini ada di path yang benar)
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Variabel default untuk mode TAMBAH
$id_ruangan = $_GET['id'] ?? null;
$ruangan_data = [
    'nama_ruangan' => '',
    'kategori_id' => '',
    'lokasi' => '',
    'kapasitas' => 0,
    'status_ruangan' => 'tersedia',
    'gambar' => ''
];
$fasilitas_ruangan = []; // Array untuk menyimpan fasilitas yang sudah dimiliki ruangan

// --- 1. AMBIL DATA KATEGORI (Dropdown) ---
$query_kategori = "SELECT id_kategori, nama_kategori FROM kategori_ruangan ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);
if (!$result_kategori) {
    die("Query Kategori Error: " . mysqli_error($koneksi));
}

// --- 2. AMBIL SEMUA FASILITAS (Checklist) ---
$query_fasilitas_list = "SELECT id_fasilitas, nama_fasilitas FROM fasilitas ORDER BY nama_fasilitas ASC";
$result_fasilitas_list = mysqli_query($koneksi, $query_fasilitas_list);
if (!$result_fasilitas_list) {
    die("Query Fasilitas List Error: " . mysqli_error($koneksi));
}


// --- 3. LOGIKA UNTUK MODE EDIT (Jika ada ID) ---
if ($id_ruangan) {
    // A. Ambil data utama ruangan
    $query_ruangan = "SELECT * FROM ruangan WHERE id_ruangan = '" . mysqli_real_escape_string($koneksi, $id_ruangan) . "'";
    $result_ruangan = mysqli_query($koneksi, $query_ruangan);

    if (mysqli_num_rows($result_ruangan) == 0) {
        // Redirect atau tampilkan pesan jika ID tidak valid
        header('Location: ruangan.php'); 
        exit;
    }
    $ruangan_data = mysqli_fetch_assoc($result_ruangan);

    // B. Ambil data relasi fasilitas ruangan
    $query_fasilitas_relasi = "
        SELECT 
            fasilitas_id, 
            jumlah 
        FROM 
            ruangan_fasilitas 
        WHERE 
            ruangan_id = '" . mysqli_real_escape_string($koneksi, $id_ruangan) . "'";
            
    $result_fasilitas_relasi = mysqli_query($koneksi, $query_fasilitas_relasi);
    
    // Konversi hasil relasi menjadi array asosiatif (id_fasilitas => jumlah)
    while ($relasi = mysqli_fetch_assoc($result_fasilitas_relasi)) {
        $fasilitas_ruangan[$relasi['fasilitas_id']] = $relasi['jumlah'];
    }
}

$form_title = $id_ruangan ? "Edit Data Ruangan: " . htmlspecialchars($ruangan_data['nama_ruangan']) : "Tambah Data Ruangan Baru";
?>

<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Ruangan /</span> <?= $form_title; ?></h4>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Form Ruangan</h5>
                </div>
                <div class="card-body">
                    
                    <form action="proses_ruangan.php" method="POST" enctype="multipart/form-data">
                        <?php if ($id_ruangan): ?>
                            <input type="hidden" name="id_ruangan" value="<?= htmlspecialchars($id_ruangan); ?>">
                            <input type="hidden" name="aksi" value="edit">
                        <?php else: ?>
                            <input type="hidden" name="aksi" value="tambah">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label" for="nama_ruangan">Nama Ruangan</label>
                            <input type="text" 
                                class="form-control" 
                                id="nama_ruangan" 
                                name="nama_ruangan" 
                                value="<?= htmlspecialchars($ruangan_data['nama_ruangan']); ?>" 
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="kategori_id">Kategori</label>
                            <select id="kategori_id" name="kategori_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php 
                                mysqli_data_seek($result_kategori, 0); // Reset pointer
                                while ($kategori = mysqli_fetch_assoc($result_kategori)): 
                                    $selected = ($ruangan_data['kategori_id'] == $kategori['id_kategori']) ? 'selected' : '';
                                ?>
                                    <option value="<?= htmlspecialchars($kategori['id_kategori']); ?>" <?= $selected; ?>>
                                        <?= htmlspecialchars($kategori['nama_kategori']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label" for="lokasi">Lokasi / Gedung</label>
                            <input type="text" 
                                class="form-control" 
                                id="lokasi" 
                                name="lokasi" 
                                value="<?= htmlspecialchars($ruangan_data['lokasi']); ?>" 
                                required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="kapasitas">Kapasitas (Orang)</label>
                                <input type="number" 
                                    class="form-control" 
                                    id="kapasitas" 
                                    name="kapasitas" 
                                    value="<?= htmlspecialchars($ruangan_data['kapasitas']); ?>" 
                                    min="1"
                                    required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="status_ruangan">Status Ruangan</label>
                                <select id="status_ruangan" name="status_ruangan" class="form-select" required>
                                    <?php 
                                    $statuses = ['tersedia', 'tidak_tersedia', 'dalam_perbaikan'];
                                    foreach ($statuses as $status): 
                                        $display = str_replace('_', ' ', ucwords($status));
                                        $selected = ($ruangan_data['status_ruangan'] == $status) ? 'selected' : '';
                                    ?>
                                        <option value="<?= $status; ?>" <?= $selected; ?>><?= $display; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="gambar" class="form-label">Gambar/Foto Ruangan</label>
                            <input class="form-control" type="file" id="gambar" name="gambar" <?= $id_ruangan ? '' : 'required'; ?>>
                            <?php if ($ruangan_data['gambar']): ?>
                                <small class="text-muted mt-2 d-block">Gambar saat ini: <?= htmlspecialchars($ruangan_data['gambar']); ?></small>
                                <input type="hidden" name="gambar_lama" value="<?= htmlspecialchars($ruangan_data['gambar']); ?>">
                            <?php endif; ?>
                        </div>


                        <h6 class="mt-4 mb-3 text-primary"><i class="bx bx-check-square me-1"></i> Detail Fasilitas</h6>
                        <div class="row mb-3 border p-3 rounded">
                            
                            <?php
                            // Reset pointer fasilitas list
                            mysqli_data_seek($result_fasilitas_list, 0); 

                            // Loop semua fasilitas yang tersedia
                            while ($fasilitas = mysqli_fetch_assoc($result_fasilitas_list)):
                                $fasilitas_id = $fasilitas['id_fasilitas'];
                                $is_checked = isset($fasilitas_ruangan[$fasilitas_id]);
                                $jumlah_value = $is_checked ? $fasilitas_ruangan[$fasilitas_id] : 1;
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <input class="form-check-input mt-0 fasilitas-checkbox" 
                                                type="checkbox" 
                                                name="fasilitas_id[]" 
                                                value="<?= $fasilitas_id; ?>" 
                                                id="fasilitas_<?= $fasilitas_id; ?>"
                                                data-target-input="#jumlah_<?= $fasilitas_id; ?>"
                                                <?= $is_checked ? 'checked' : ''; ?>>
                                    </span>
                                    
                                    <label for="fasilitas_<?= $fasilitas_id; ?>" class="input-group-text bg-light border-end-0" style="width: 50%;">
                                        <?= htmlspecialchars($fasilitas['nama_fasilitas']); ?>
                                    </label>

                                    <input type="number" 
                                           class="form-control" 
                                           name="jumlah_fasilitas[<?= $fasilitas_id; ?>]" 
                                           id="jumlah_<?= $fasilitas_id; ?>"
                                           value="<?= $jumlah_value; ?>" 
                                           min="1" 
                                           placeholder="Jumlah"
                                           title="Jumlah fasilitas"
                                           <?= !$is_checked ? 'disabled' : ''; ?>>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <small class="form-text text-muted">Centang fasilitas yang tersedia dan masukkan jumlahnya.</small>
                        <hr>
                        
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bx bx-save me-1"></i> Simpan Data
                        </button>
                        <a href="ruangan.php" class="btn btn-outline-secondary">Batal</a>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkboxes = document.querySelectorAll('.fasilitas-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const targetId = this.getAttribute('data-target-input');
                const targetInput = document.querySelector(targetId);
                
                if (this.checked) {
                    targetInput.removeAttribute('disabled');
                    // Pastikan nilainya minimal 1 saat diaktifkan
                    if (targetInput.value < 1) {
                         targetInput.value = 1;
                    }
                } else {
                    targetInput.setAttribute('disabled', 'disabled');
                    // Opsional: reset nilai saat dinonaktifkan
                    // targetInput.value = 1; 
                }
            });
        });
    });
</script>

<?php
// ... (Bagian SweetAlert dan Footer) ...
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