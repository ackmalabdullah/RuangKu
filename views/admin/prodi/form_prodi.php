<?php
$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$database = new Database();
$koneksi = $database->conn;

// Default (mode tambah)
$mode = 'tambah';
$judul_halaman = 'Tambah Program Studi';
$tombol_submit = 'Simpan';
$form_action = 'proses_prodi.php?aksi=tambah';

// Data default
$data = [
    'id_prodi' => '',
    'nama_prodi' => ''
];

// Jika mode edit
if (isset($_GET['id'])) {
    $mode = 'edit';
    $id_prodi = $_GET['id'];
    $judul_halaman = 'Edit Program Studi';
    $tombol_submit = 'Update';
    $form_action = 'proses_prodi.php?aksi=edit';

    // Ambil data prodi berdasarkan ID
    $stmt = $koneksi->prepare("SELECT id_prodi, nama_prodi FROM prodi WHERE id_prodi = ?");
    $stmt->bind_param("i", $id_prodi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        echo "Error: Data tidak ditemukan!";
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
          <a href="prodi_lab.php" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>

        <div class="card-body">
          <form action="<?= $form_action; ?>" method="POST">

            <?php if ($mode == 'edit'): ?>
              <input type="hidden" name="id_prodi" value="<?= $data['id_prodi']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="nama_prodi">Nama Prodi</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-book-content"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="nama_prodi"
                    name="nama_prodi"
                    placeholder="Masukkan nama program studi (contoh: Informatika, Akuntansi)"
                    value="<?= htmlspecialchars($data['nama_prodi']); ?>"
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
