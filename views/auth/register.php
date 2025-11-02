<?php
// Kita perlu koneksi DB untuk mengambil daftar prodi
require '../../settings/koneksi.php';
$database = new Database();
$conn = $database->conn;

// Ambil semua prodi dari tabel prodi
$list_prodi = [];
try {
  $sql_prodi = "SELECT id_prodi, nama_prodi FROM prodi ORDER BY nama_prodi ASC";
  $result_prodi = $conn->query($sql_prodi);
  if ($result_prodi->num_rows > 0) {
    while ($row = $result_prodi->fetch_assoc()) {
      $list_prodi[] = $row;
    }
  }
} catch (Exception $e) {
  // Biarkan $list_prodi kosong jika tabel prodi gagal dimuat
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Sistem Informasi</title>

  <link rel="stylesheet" href="../../assets/css/register.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
  <div class="login-container">
    <div class="logo-container">
      <img src="../../assets/img/logo.png" alt="Logo Instansi">
    </div>

    <form id="registerForm" action="proses_register.php" method="POST" class="login-form" novalidate>
      <h2>Register Mahasiswa</h2>
      <p>Silakan isi data diri Anda untuk mendaftar.</p>

      <div class="input-group">
        <label for="nama">Nama Lengkap</label>
        <div class="input-with-icon">
          <i class="fas fa-user"></i>
          <input type="text" id="nama" name="nama" placeholder="Masukkan nama lengkap Anda">
        </div>
        <div id="namaError" class="error-message">Nama tidak boleh kosong.</div>
      </div>

      <div class="input-group">
        <label for="prodi_id">Program Studi</label>
        <div class="input-with-icon">
          <i class="fas fa-graduation-cap"></i>
          <select id="prodi_id" name="prodi_id">
            <option value="">-- Pilih Program Studi --</option>
            <?php foreach ($list_prodi as $prodi): ?>
              <option value="<?php echo htmlspecialchars($prodi['id_prodi']); ?>">
                <?php echo htmlspecialchars($prodi['nama_prodi']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div id="prodiError" class="error-message">Prodi harus dipilih.</div>
      </div>

      <div class="input-group">
        <label for="nim">NIM</label>
        <div class="input-with-icon">
          <i class="fas fa-id-card"></i>
          <input type="text" id="nim" name="nim" placeholder="Masukkan NIM Anda">
        </div>
        <div id="nimError" class="error-message">NIM tidak boleh kosong.</div>
      </div>

      <div class="input-group">
        <label for="angkatan">Angkatan</label>
        <div class="input-with-icon">
          <i class="fas fa-calendar-alt"></i>
          <input type="number" id="angkatan" name="angkatan" placeholder="Contoh: 2024" min="2000" max="<?php echo date('Y'); ?>">
        </div>
        <div id="angkatanError" class="error-message">Angkatan tidak boleh kosong.</div>
      </div>

      <div class="input-group">
        <label for="email">Email</label>
        <div class="input-with-icon">
          <i class="fas fa-envelope"></i>
          <input type="email" id="email" name="email" placeholder="Masukkan email aktif Anda">
        </div>
        <div id="emailError" class="error-message">Email tidak boleh kosong atau format salah.</div>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock"></i> <input type="password" id="password" name="password" placeholder="Buat password Anda">
          <i class="fas fa-eye-slash toggle-password"></i>
        </div>
        <div id="passwordError" class="error-message">Password tidak boleh kosong.</div>
      </div>

      <div class="input-group">
        <label for="konfirmasi_password">Konfirmasi Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock"></i> <input type="password" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi password Anda">
          <i class="fas fa-eye-slash toggle-password"></i>
        </div>
        <div id="konfirmasiPasswordError" class="error-message">Konfirmasi password tidak boleh kosong.</div>
      </div>

      <button type="submit" class="login-button">Daftar</button>

      <div class="support-link">
        <p>Sudah punya akun? <a href="login_mahasiswa.php">Login di sini</a></p>
      </div>
      <div class="support-link">
        <p>Login Sebagai Admin atau Pengelola <a href="login.php">Login di sini</a></p>
      </div>
    </form>
  </div>

  <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
      let valid = true;
      const prodi = document.getElementById('prodi_id');
      const angkatan = document.getElementById('angkatan');
      const prodiError = document.getElementById('prodiError');
      const angkatanError = document.getElementById('angkatanError');
      const nama = document.getElementById('nama');
      const nim = document.getElementById('nim');
      const email = document.getElementById('email');
      const password = document.getElementById('password');
      const konfirmasi_password = document.getElementById('konfirmasi_password');
      const namaError = document.getElementById('namaError');
      const nimError = document.getElementById('nimError');
      const emailError = document.getElementById('emailError');
      const passwordError = document.getElementById('passwordError');
      const konfirmasiPasswordError = document.getElementById('konfirmasiPasswordError');

      namaError.style.display = 'none';
      prodiError.style.display = 'none';
      nimError.style.display = 'none';
      angkatanError.style.display = 'none';
      emailError.style.display = 'none';
      passwordError.style.display = 'none';
      konfirmasiPasswordError.style.display = 'none';

      if (nama.value.trim() === '') {
        namaError.style.display = 'block';
        valid = false;
      }
      if (prodi.value.trim() === '') {
        prodiError.style.display = 'block';
        valid = false;
      }
      if (nim.value.trim() === '') {
        nimError.style.display = 'block';
        valid = false;
      }
      if (angkatan.value.trim() === '') {
        angkatanError.style.display = 'block';
        valid = false;
      }
      if (email.value.trim() === '') {
        emailError.style.display = 'block';
        valid = false;
      }
      if (password.value.trim() === '') {
        passwordError.style.display = 'block';
        valid = false;
      }
      if (konfirmasi_password.value.trim() === '') {
        konfirmasiPasswordError.style.display = 'block';
        konfirmasiPasswordError.innerText = 'Konfirmasi password tidak boleh kosong.';
        valid = false;
      }
      if (valid && password.value.trim() !== konfirmasi_password.value.trim()) {
        konfirmasiPasswordError.style.display = 'block';
        konfirmasiPasswordError.innerText = 'Password dan konfirmasi password tidak cocok.';
        valid = false;
      }
      if (!valid) {
        e.preventDefault();
      }
    });
  </script>

  <script>
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(button => {
      button.addEventListener('click', function() {
        const inputField = this.parentElement.querySelector('input');

        if (inputField.type === 'password') {
          inputField.type = 'text';
          this.classList.remove('fa-eye-slash');
          this.classList.add('fa-eye');
        } else {
          inputField.type = 'password';
          this.classList.remove('fa-eye');
          this.classList.add('fa-eye-slash');
        }
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

  <?php
  if (isset($_GET['message'])) {
    $message_js = json_encode($_GET['message']);
    echo "<script>Swal.fire({title: 'Berhasil!', text: $message_js, icon: 'success', confirmButtonText: 'OK'});</script>";
  }
  if (isset($_GET['error'])) {
    $error_js = json_encode($_GET['error']);
    echo "<script>Swal.fire({title: 'Gagal!', text: $error_js, icon: 'error', confirmButtonText: 'Coba Lagi'});</script>";
  }
  $conn->close();
  ?>
</body>

</html>