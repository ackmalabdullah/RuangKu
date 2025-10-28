<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Sistem Informasi</title>

  <link rel="stylesheet" href="../../assets/css/login.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>

  <div class="login-container">

    <div class="logo-container">
      <img src="../../assets/img/logo.png" alt="Logo Instansi">
    </div>

    <form action="proses_login.php" method="POST" class="login-form">
      <h2>Login</h2>
      <p>Silakan masuk menggunakan akun terdaftar Anda.</p>

      <div class="input-group">
        <label for="username">Username</label>
        <div class="input-with-icon">
          <i class="fas fa-user"></i>
          <input type="text" id="username" name="username" placeholder="Masukkan NIP/Email/Username">
        </div>
      </div>

      <div class="input-group">
        <label for="password">Password</label>
        <div class="input-with-icon">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="Masukkan password Anda">
        </div>
      </div>

      <div class="options">
        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Ingat Saya</label>
        </div>
        <a href="#" class="forgot-password">Lupa Password?</a>
      </div>

      <button type="submit" class="login-button">Login</button>

      <div class="support-link">
        <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
      </div>
    </form>
  </div>

</body>

</html>