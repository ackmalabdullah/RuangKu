<?php
$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$database = new Database();
$koneksi = $database->conn;

// DEFAULT (TAMBAH)
$mode = 'tambah';
$judul_halaman = 'Tambah Data Mahasiswa';
$tombol_submit = 'Simpan';
$form_action = 'proses_mahasiswa.php?aksi=tambah';

// DATA DEFAULT
$data = [
  'id_mahasiswa' => '',
  'prodi_id' => '',
  'nim' => '',
  'nama' => '',
  'email' => '',
  'angkatan' => '',
  'foto' => ''
];

// MODE EDIT
if (isset($_GET['id'])) {
    $mode = 'edit';
    $id_mahasiswa = $_GET['id'];
    $judul_halaman = 'Edit Data Mahasiswa';
    $tombol_submit = 'Update';
    $form_action = 'proses_mahasiswa.php?aksi=edit&id=' . $id_mahasiswa;

    //WAJIB ADA — ambil datanya dari DB
    $stmt = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
    $stmt->bind_param("i", $id_mahasiswa);
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

// AMBIL PRODI
$prodi = $koneksi->query("SELECT id_prodi, nama_prodi FROM prodi");
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <div class="row">
    <div class="col-xxl">


      <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between">
          <h5 class="mb-0"><?= $judul_halaman; ?></h5>
          <a href="mahasiswa.php" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>

        <div class="card-body">

          <form action="<?= $form_action; ?>" method="POST" enctype="multipart/form-data">

            <?php if ($mode == 'edit'): ?>
              <input type="hidden" name="id_mahasiswa" value="<?= $data['id_mahasiswa']; ?>">
              <input type="hidden" name="foto_lama" value="<?= $data['foto']; ?>">
            <?php endif; ?>

            <!-- PRODI -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Program Studi</label>
              <div class="col-sm-10">
                <select name="prodi_id" class="form-select" required>
                  <option value="">-- Pilih Prodi --</option>
                  <?php while ($p = $prodi->fetch_assoc()): ?>
                    <option value="<?= $p['id_prodi']; ?>"
                      <?= ($data['prodi_id'] == $p['id_prodi']) ? 'selected' : ''; ?>>
                      <?= $p['nama_prodi']; ?>
                    </option>
                  <?php endwhile; ?>
                </select>
              </div>
            </div>

            <!-- NIM -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">NIM</label>
              <div class="col-sm-10">
                <input type="text" name="nim" class="form-control"
                  value="<?= htmlspecialchars($data['nim']); ?>" required>
              </div>
            </div>

            <!-- NAMA -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Nama Lengkap</label>
              <div class="col-sm-10">
                <input type="text" name="nama" class="form-control"
                  value="<?= htmlspecialchars($data['nama']); ?>" required>
              </div>
            </div>

            <!-- EMAIL -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-10">
                <input type="email" name="email" class="form-control"
                  value="<?= htmlspecialchars($data['email']); ?>" required>
              </div>
            </div>

            <!-- PASSWORD -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Password</label>
              <div class="col-sm-10">
                <input type="password" name="password" class="form-control" <?= ($mode == 'tambah') ? 'required' : ''; ?>>
                <?php if ($mode == 'edit'): ?>
                  <div class="form-text text-warning">
                    * Kosongkan jika tidak ingin mengubah password.
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- ANGKATAN -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Angkatan</label>
              <div class="col-sm-10">
                <input type="number" name="angkatan" class="form-control"
                  value="<?= htmlspecialchars($data['angkatan']); ?>" required>
              </div>
            </div>

            <!-- FOTO -->
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Foto</label>
              <div class="col-sm-10">
                <input type="file" name="foto" class="form-control">

                <?php if ($mode == 'edit' && !empty($data['foto'])): ?>
                  <div class="mt-2">
                    <!-- Gunakan relative path yang lebih fleksibel untuk foto -->
                    <img src="../../../../assets/img/avatars/<?= htmlspecialchars($data['foto']); ?>"
                      alt="Foto" width="120" class="rounded">
                  </div>
                <?php endif; ?>
              </div>
            </div>

            <!-- SUBMIT -->
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
