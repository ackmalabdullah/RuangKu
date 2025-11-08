<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password Mahasiswa</title>

    <link rel="stylesheet" href="../../assets/css/login.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        /* Tambahan sedikit gaya untuk pesan error, disesuaikan dengan login.php */
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
            <img src="../../assets/img/logo.png" alt="Logo Instansi">
        </div>

        <form id="forgotPasswordForm" action="proses_lupa_password.php" method="POST" class="login-form" novalidate>
            <h2>Lupa Password Mahasiswa</h2>
            <p>Masukkan alamat email Anda yang terdaftar. Kami akan mengirimkan tautan reset password.</p>

            <div class="input-group">
                <label for="email">Email</label>
                <div class="input-with-icon">
                    <i class="fas fa-envelope"></i> 
                    <input type="email" id="email" name="email" placeholder="Masukkan email terdaftar Anda" required>
                </div>
                <div id="emailError" class="error-message">Email tidak boleh kosong.</div>
            </div>

            <input type="hidden" name="user_type" value="mahasiswa">

            <button type="submit" class="login-button">Kirim Tautan Reset</button>

            <div class="support-link">
                <p><a href="login_mahasiswa.php">Kembali ke Halaman Login</a></p>
            </div>
        </form>
        
    </div>

    <script>
        document.getElementById('forgotPasswordForm').addEventListener('submit', function (e) {
            let valid = true;
            const email = document.getElementById('email');
            const emailError = document.getElementById('emailError');
            
            emailError.style.display = 'none';

            if (email.value.trim() === '') {
                emailError.style.display = 'block';
                valid = false;
            }
            if (!valid) {
                e.preventDefault();
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <?php
    // Menampilkan pesan SUKSES atau ERROR setelah submit (dari proses_lupa_password.php)
    if (isset($_GET['message'])) {
        $message_js = json_encode($_GET['message']);
    ?>
        <script>
            Swal.fire({
                title: 'Informasi',
                text: <?php echo $message_js; ?>,
                icon: 'info', // Gunakan 'info' atau 'success' tergantung pesan
                confirmButtonText: 'OK'
            });
        </script>
    <?php
    }
    ?>

</body>
</html>