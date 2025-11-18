<?php
$required_role = 'mahasiswa';

// 1. Memuat layout utama (header.php sudah mengurus koneksi & $koneksi)
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// 3. Logika PHP untuk mengambil data halaman ini
// Ambil ID mahasiswa dari session yang login
$id_mahasiswa_login = $_SESSION['id_mahasiswa'];

// Query untuk mengambil data mahasiswa yang sedang login
$stmt_mhs = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt_mhs->bind_param("i", $id_mahasiswa_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();
$mhs = $result_mhs->fetch_assoc();
$stmt_mhs->close(); // Tutup statement

// Query untuk mengambil daftar prodi
$query_prodi = mysqli_query($koneksi, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC");

// Variabel untuk menentukan sumber foto profil
$path_ke_foto = "../../../assets/img/avatars/";
$foto_default = "default.png";

if (isset($mhs['foto']) && !empty($mhs['foto']) && file_exists($path_ke_foto . $mhs['foto'])) {
  $foto_tampil = $mhs['foto'];
} else {
  $foto_tampil = $foto_default;
}
$src_foto_profil = $path_ke_foto . $foto_tampil;
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Profile /</span> Profile</h4>

  <div class="row">
    <div class="col-md-12">
      <div class="card mb-4">
        <h5 class="card-header">Profile Details</h5>

        <form id="formAccountSettings" method="POST" action="proses_profile.php" enctype="multipart/form-data">
          <div class="card-body">
            <div class="d-flex align-items-start align-items-sm-center gap-4">

              <img
                src="<?= $src_foto_profil; ?>"
                alt="user-avatar"
                class="d-block rounded"
                height="100"
                width="100"
                id="uploadedAvatar"
                style="object-fit: cover; border-radius: 50%;" />

              <div class="button-wrapper">
                <label for="upload" class="btn btn-primary me-2 mb-4" tabindex="0">
                  <span class="d-none d-sm-block">Upload foto baru</span>
                  <i class="bx bx-upload d-block d-sm-none"></i>
                  <input
                    type="file"
                    id="upload"
                    class="account-file-input"
                    hidden
                    accept="image/png, image/jpeg"
                    name="foto_profil" />
                </label>
                <button type="button" class="btn btn-outline-secondary account-image-reset mb-4">
                  <i class="bx bx-reset d-block d-sm-none"></i>
                  <span class="d-none d-sm-block">Reset</span>
                </button>

                <p class="text-muted mb-0">Hanya JPG atau PNG. Ukuran maks 800K.</p>
              </div>
            </div>
          </div>
          <hr class="my-0" />
          <div class="card-body">

            <input type="hidden" name="id_mahasiswa" value="<?= $mhs['id_mahasiswa']; ?>">

            <div class="row">
              <div class="mb-3 col-md-6">
                <label for="nama" class="form-label">Nama Lengkap</label>
                <input
                  class="form-control"
                  type="text"
                  id="nama"
                  name="nama"
                  value="<?= htmlspecialchars($mhs['nama']); ?>"
                  autofocus />
              </div>

              <div class="mb-3 col-md-6">
                <label for="nim" class="form-label">NIM</label>
                <input class="form-control" type="text" name="nim" id="nim" value="<?= htmlspecialchars($mhs['nim']); ?>" readonly />
              </div>

              <div class="mb-3 col-md-6">
                <label for="email" class="form-label">E-mail</label>
                <input
                  class="form-control"
                  type="email"
                  id="email"
                  name="email"
                  value="<?= htmlspecialchars($mhs['email']); ?>"
                  placeholder="john.doe@example.com" />
              </div>

              <div class="mb-3 col-md-6">
                <label for="angkatan" class="form-label">Angkatan</label>
                <input
                  type="number"
                  class="form-control"
                  id="angkatan"
                  name="angkatan"
                  value="<?= htmlspecialchars($mhs['angkatan']); ?>" />
              </div>

              <div class="mb-3 col-md-6">
                <label class="form-label" for="prodi_id">Program Studi</label>
                <select id="prodi_id" name="prodi_id" class="select2 form-select">
                  <option value="">Pilih Program Studi</option>
                  <?php
                  while ($prodi = mysqli_fetch_assoc($query_prodi)) {
                    $selected = ($prodi['id_prodi'] == $mhs['prodi_id']) ? 'selected' : '';
                    echo "<option value='" . $prodi['id_prodi'] . "' $selected>" . htmlspecialchars($prodi['nama_prodi']) . "</option>";
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="mt-2">
              <button type="submit" name="simpan_profil" class="btn btn-primary me-2">Save changes</button>
              <button type="reset" class="btn btn-outline-secondary">Cancel</button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // 1. Ambil elemen-elemen DOM
    const uploadInput = document.getElementById('upload');
    const uploadedAvatar = document.getElementById('uploadedAvatar');
    const resetButton = document.querySelector('.account-image-reset');

    const originalAvatarSrc = uploadedAvatar.src;

    // --- 2. Logika saat file baru dipilih (untuk preview) ---
    if (uploadInput) {
      uploadInput.addEventListener('change', function(event) {
        const file = event.target.files[0];

        if (!file) return;

        // Cek apakah itu file gambar
        if (!file.type.startsWith('image/')) {
          alert('File yang dipilih bukan gambar. Silakan pilih file JPG atau PNG.');
          uploadInput.value = '';
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          uploadedAvatar.src = e.target.result;
        };
        reader.readAsDataURL(file);
      });
    }

    // --- 3. Logika untuk Tombol Reset ---
    if (resetButton) {
      resetButton.addEventListener('click', function() {
        uploadedAvatar.src = originalAvatarSrc;
        uploadInput.value = '';
      });
    }
  });
</script>
<?php
// 5. Logika SweetAlert tetap di sini (ini menerima pesan dari proses_profile.php)
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
  unset($_SESSION['pesan']);
}

// 6. Memuat footer
require '../../../partials/mahasiswa/footer.php';
?>