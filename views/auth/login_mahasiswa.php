<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistem Informasi</title>

  <link rel="stylesheet" href="../../assets/css/login.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

  <style>
    /* Tambahan sedikit gaya untuk pesan error */
    .error-message {
      color: #ff4d4d;
      font-size: 0.9em;
      margin-top: 4px;
      display: none;
    }
  </style>
</head>

<body>

  <div class="login-container">

    <div class="logo-container">
      <img src="../../assets/img/logo kotak.png" alt="Logo Instansi">
    </div>

    <form id="loginForm" action="proses_login_mahasiswa.php" method="POST" class="login-form" novalidate>
      <h2>Login Mahasiswa</h2>
      <p>Silakan masuk menggunakan akun terdaftar Anda.</p>

      <div class="input-group">
        <label for="email">Email</label>
        <div class="input-with-icon">
          <i class="fas fa-user"></i>
          <input type="text" id="email" name="email" placeholder="Masukkan NIP/Email/Username">
        </div>
        <div id="emailError" class="error-message">Email/NIP/Username tidak boleh kosong.</div>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock"></i> <input type="password" id="password" name="password" placeholder="Masukkan Password Anda">
          <i class="fas fa-eye-slash toggle-password"></i>
        </div>
        <div id="passwordError" class="error-message">Password tidak boleh kosong.</div>
      </div>

      <button type="submit" class="login-button">Login</button>

      <div class="support-link">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
      </div>
      <div class="support-link">
        <p>Login Sebagai Admin, Pengelola, dan Jurusan <a href="login.php">Login di sini</a></p>
      </div>
    </form>
  </div>

  <script>
    document.getElementById('loginForm').addEventListener('submit', function (e) {
      let valid = true;
      const email = document.getElementById('email');
      const password = document.getElementById('password');
      const emailError = document.getElementById('emailError');
      const passwordError = document.getElementById('passwordError');
      emailError.style.display = 'none';
      passwordError.style.display = 'none';
      if (email.value.trim() === '') {
        emailError.style.display = 'block';
        valid = false;
      }
      if (password.value.trim() === '') {
        passwordError.style.display = 'block';
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
  // Menampilkan pesan SUKSES (dari logout)
  if (isset($_GET['message'])) {
      $message_js = json_encode($_GET['message']);
  ?>
      <script>
          Swal.fire({
              title: 'Berhasil!',
              text: <?php echo $message_js; ?>,
              icon: 'success',
              confirmButtonText: 'OK'
          });
      </script>
  <?php
  } // Akhir 'if message'

  // Menampilkan pesan ERROR (dari proses_login_mahasiswa.php)
  if (isset($_GET['error'])) {
      $error_js = json_encode($_GET['error']);
  ?>
      <script>
          Swal.fire({
              title: 'Gagal!',
              text: <?php echo $error_js; ?>,
              icon: 'error',
              confirmButtonText: 'Coba Lagi'
          });
      </script>
  <?php
  } // Akhir 'if error'
  ?>

</body>
</html>