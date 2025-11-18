<?php
$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
// require '../../../settings/koneksi.php'; 

$database = new Database();
$koneksi = $database->conn;

// Inisialisasi variabel
$mode = 'tambah';
$judul_halaman = 'Tambah Kategori Ruangan';
$tombol_submit = 'Simpan';
$form_action = 'proses_kategori.php?aksi=tambah';

// Data default untuk form tambah
$data = [
    'id_kategori' => '',
    'nama_kategori' => ''
];

// Cek apakah ini mode EDIT (jika ada ID di URL)
if (isset($_GET['id'])) {
    $mode = 'edit';
    $id_kategori = $_GET['id'];
    $judul_halaman = 'Edit Kategori Ruangan';
    $tombol_submit = 'Update';
    $form_action = 'proses_kategori.php?aksi=edit';

    // Ambil data dari database
    $stmt = $koneksi->prepare("SELECT id_kategori, nama_kategori FROM kategori_ruangan WHERE id_kategori = ?");
    $stmt->bind_param("i", $id_kategori);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Error: Data tidak ditemukan.";
        exit;
    }
    $stmt->close();
}
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-xxl">
      
      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><?= $judul_halaman; ?></h5>
          <a href="kategori.php" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>

        <div class="card-body">
          <form action="<?= $form_action; ?>" method="POST">
            
            <?php if ($mode == 'edit'): ?>
              <input type="hidden" name="id_kategori" value="<?= $data['id_kategori']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="nama_kategori">Nama Kategori</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bxs-building-house"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="nama_kategori"
                    name="nama_kategori"
                    placeholder="Masukkan nama kategori ruangan"
                    value="<?= htmlspecialchars($data['nama_kategori']); ?>"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="row justify-content-end">
              <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">
                  <i class="bx bx-save me-1"></i> <?= $tombol_submit; ?>
                </button>
              </div>
            </div>

          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<?php
require '../../../partials/mahasiswa/footer.php';
?>
