<?php
$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
// require '../../../settings/koneksi.php';

$database = new Database();
$koneksi = $database->conn;

// Default (mode tambah)
$mode = 'tambah';
$judul_halaman = 'Tambah Fasilitas';
$tombol_submit = 'Simpan';
$form_action = 'proses_fasilitas.php?aksi=tambah';

// Data default
$data = [
    'id_fasilitas' => '',
    'nama_fasilitas' => ''
];

// Jika mode edit
if (isset($_GET['id'])) {
    $mode = 'edit';
    $id_fasilitas = $_GET['id'];
    $judul_halaman = 'Edit Fasilitas';
    $tombol_submit = 'Update';
    $form_action = 'proses_fasilitas.php?aksi=edit';

    // Ambil data fasilitas berdasarkan ID
    $stmt = $koneksi->prepare("SELECT id_fasilitas, nama_fasilitas FROM fasilitas WHERE id_fasilitas = ?");
    $stmt->bind_param("i", $id_fasilitas);
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
          <a href="fasilitas_lab.php" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>

        <div class="card-body">
          <form action="<?= $form_action; ?>" method="POST">

            <?php if ($mode == 'edit'): ?>
              <input type="hidden" name="id_fasilitas" value="<?= $data['id_fasilitas']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="nama_fasilitas">Nama Fasilitas</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-list-check"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="nama_fasilitas"
                    name="nama_fasilitas"
                    placeholder="Masukkan nama fasilitas (contoh: AC, Proyektor, WiFi)"
                    value="<?= htmlspecialchars($data['nama_fasilitas']); ?>"
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
