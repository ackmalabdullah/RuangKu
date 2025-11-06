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
$judul_halaman = 'Tambah Data Pengelola';
$tombol_submit = 'Simpan';
$form_action = 'proses_pengelola.php?aksi=tambah';

// Data default untuk form tambah
$data = [
    'id_user' => '',
    'nama' => '',
    'email' => '',
    'username' => '',
    'role' => 'pengelola_ruangan' // Default role
];

// Cek apakah ini mode EDIT (jika ada ID di URL)
if (isset($_GET['id'])) {
    $mode = 'edit';
    $id_user = $_GET['id'];
    $judul_halaman = 'Edit Data Pengelola';
    $tombol_submit = 'Update';
    $form_action = 'proses_pengelola.php?aksi=edit';

    // Ambil data user dari database
    $stmt = $koneksi->prepare("SELECT id_user, nama, email, username, role FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    } else {
        // Jika ID tidak ditemukan, bisa ditangani di sini (misal: redirect)
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
          <a href="pengelola.php" class="btn btn-sm btn-secondary">
            <i class="bx bx-arrow-back me-1"></i> Kembali
          </a>
        </div>
        <div class="card-body">
          
          <form action="<?= $form_action; ?>" method="POST">
            
            <?php if ($mode == 'edit'): ?>
              <input type="hidden" name="id_user" value="<?= $data['id_user']; ?>">
            <?php endif; ?>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="nama">Nama Lengkap</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-user"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="nama"
                    name="nama"
                    placeholder="Masukkan nama lengkap"
                    value="<?= htmlspecialchars($data['nama']); ?>"
                    required
                  />
                </div>
              </div>
            </div>
            
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="email">Email</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-envelope"></i></span>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    placeholder="contoh@gmail.com"
                    value="<?= htmlspecialchars($data['email']); ?>"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="username">Username</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-user-circle"></i></span>
                  <input
                    type="text"
                    class="form-control"
                    id="username"
                    name="username"
                    placeholder="Masukkan username untuk login"
                    value="<?= htmlspecialchars($data['username']); ?>"
                    required
                  />
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="password">Password</label>
              <div class="col-sm-10">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-key"></i></span>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
                    <?php if ($mode == 'tambah') echo 'required'; // Wajib diisi saat tambah data ?>
                  />
                </div>
                <?php if ($mode == 'edit'): ?>
                  <div class="form-text text-warning">
                    * Kosongkan jika tidak ingin mengubah password.
                  </div>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="row mb-3">
              <label class="col-sm-2 col-form-label" for="role">Role</label>
              <div class="col-sm-10">
                 <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="bx bx-check-shield"></i></span>
                  <select class="form-select" id="role" name="role" required>
                    <option value="pengelola_ruangan" <?= ($data['role'] == 'pengelola_ruangan') ? 'selected' : ''; ?>>
                      Pengelola Ruangan
                    </option>
                    <option value="pengelola_lab" <?= ($data['role'] == 'pengelola_lab') ? 'selected' : ''; ?>>
                      Pengelola Lab
                    </option>
                  </select>
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