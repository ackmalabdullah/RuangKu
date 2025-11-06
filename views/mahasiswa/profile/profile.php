<?php
$required_role = 'mahasiswa';

// 1. Memuat layout utama (header.php sudah mengurus koneksi & $koneksi)
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// 3. Logika PHP untuk mengambil data halaman ini
//    Kita BISA LANGSUNG pakai $koneksi karena sudah ada dari header.php

// Ambil ID mahasiswa dari session yang login
$id_mahasiswa_login = $_SESSION['id_mahasiswa'];

// Query untuk mengambil data mahasiswa yang sedang login
// (Kita gunakan $koneksi yang sudah ada)
$stmt_mhs = $koneksi->prepare("SELECT * FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt_mhs->bind_param("i", $id_mahasiswa_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();
$mhs = $result_mhs->fetch_assoc();

// Query untuk mengambil daftar prodi
// (Kita gunakan $koneksi yang sudah ada)
$query_prodi = mysqli_query($koneksi, "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC");

?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Account Settings /</span> Account</h4>

  <div class="row">
    <div class="col-md-12">
      <ul class="nav nav-pills flex-column flex-md-row mb-3">
        <li class="nav-item">
          <a class="nav-link active" href="javascript:void(0);"><i class="bx bx-user me-1"></i> Account</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="ganti_password.php"><i class="bx bx-lock-alt me-1"></i> Ganti Password</a>
        </li>
      </ul>
      <div class="card mb-4">
        <h5 class="card-header">Profile Details</h5>

        <form id="formAccountSettings" method="POST" action="proses_profile.php" enctype="multipart/form-data">
          <div class="card-body">
            <div class="d-flex align-items-start align-items-sm-center gap-4">

              <?php
              // Tentukan path foto
              $path_ke_foto = "../../../assets/img/avatars/";
              $foto_tampil = "default.png"; // Nama foto default Anda

              // Cek apakah kolom 'foto' ada, tidak kosong, DAN filenya benar-benar ada
              if (isset($mhs['foto']) && !empty($mhs['foto']) && file_exists($path_ke_foto . $mhs['foto'])) {
                $foto_tampil = $mhs['foto'];
              }
              ?>

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
                  type="text"
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
                  // Loop untuk menampilkan daftar prodi
                  while ($prodi = mysqli_fetch_assoc($query_prodi)) {
                    // Cek apakah prodi ini adalah prodi yang dipilih mahasiswa
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

      <div class="card">
        <h5 class="card-header">Ganti Password</h5>
        <div class="card-body">
          <form id="formGantiPassword" method="POST" action="proses_ganti_password.php">
            <input type="hidden" name="id_mahasiswa" value="<?= $mhs['id_mahasiswa']; ?>">
            <div class="row">
              <div class="mb-3 col-md-12 form-password-toggle">
                <label class="form-label" for="password_lama">Password Lama</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password_lama" class="form-control" name="password_lama" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <div class="mb-3 col-md-6 form-password-toggle">
                <label class="form-label" for="password_baru">Password Baru</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password_baru" class="form-control" name="password_baru" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <div class="mb-3 col-md-6 form-password-toggle">
                <label class="form-label" for="konfirmasi_password">Konfirmasi Password Baru</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="konfirmasi_password" class="form-control" name="konfirmasi_password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>
            </div>
            <button type="submit" name="simpan_password" class="btn btn-primary">Ubah Password</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    // 1. Ambil elemen-elemen DOM
    const uploadInput = document.getElementById('upload');
    const uploadedAvatar = document.getElementById('uploadedAvatar'); // Gambar profil yang akan di-preview
    const resetButton = document.querySelector('.account-image-reset');

    // Ambil foto asli yang dimuat oleh PHP (sebelum perubahan)
    const originalAvatarSrc = uploadedAvatar.src;

    // --- 2. Logika saat file baru dipilih (untuk preview) ---
    if (uploadInput) {
      uploadInput.addEventListener('change', function(event) {
        const file = event.target.files[0]; // Ambil file yang dipilih

        if (!file) return; // Jika tidak ada file, berhenti

        // Cek apakah itu file gambar
        if (!file.type.startsWith('image/')) {
          alert('File yang dipilih bukan gambar. Silakan pilih file JPG atau PNG.');
          uploadInput.value = ''; // Kosongkan pilihan file
          return;
        }

        // Gunakan FileReader untuk membaca file
        const reader = new FileReader();

        // Saat file selesai dibaca
        reader.onload = function(e) {
          // Ganti 'src' gambar di halaman dengan URL data baru
          uploadedAvatar.src = e.target.result;
        };

        // Baca file sebagai Data URL
        reader.readAsDataURL(file);
      });
    }

    // --- 3. Logika untuk Tombol Reset ---
    if (resetButton) {
      resetButton.addEventListener('click', function() {
        // Kembalikan foto ke src awal yang dimuat PHP (src yang datang dari header.php)
        uploadedAvatar.src = originalAvatarSrc;
        // Hapus file dari input, agar tidak ikut terkirim saat disubmit
        uploadInput.value = '';
      });
    }
  });
</script>
<?php
// 5. Logika SweetAlert (diletakkan sebelum footer)
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


// 6. Memuat footer
require '../../../partials/mahasiswa/footer.php';
?>