<?php
// Ganti path jika koneksi.php dan PHPMailer berbeda
require_once 'koneksi.php'; 
require_once '../vendor/PHPMailer/PHPMailerAutoload.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $email = $_POST['email'] ?? '';
    // Role sudah pasti 'mahasiswa' dan dikirim dari hidden field di form
    $role = $_POST['user_type'] ?? 'mahasiswa'; 
    $response = "";
    
    if ($role !== 'mahasiswa' || empty($email)) {
        header("Location: lupa_password_mahasiswa.php?message=" . urlencode("Permintaan tidak valid."));
        exit();
    }

    try {
        // 1. Cek Email di Tabel Mahasiswa
        $stmt = $pdo->prepare("SELECT email FROM mahasiswa WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            
            // 2. Buat dan Simpan Token Unik
            $token = bin2hex(random_bytes(50)); 
            $waktu_sekarang = date("Y-m-d H:i:s");
            $waktu_kadaluarsa = date("Y-m-d H:i:s", time() + 3600); 
            
            // Hapus token lama dan simpan token baru di tabel password_reset_temp
            $pdo->prepare("DELETE FROM password_reset_temp WHERE email = ?")->execute([$email]); 
            $sql = "INSERT INTO password_reset_temp (email, token, role, created_at, expires_at) VALUES (?, ?, ?, ?, ?)";
            $pdo->prepare($sql)->execute([$email, $token, $role, $waktu_sekarang, $waktu_kadaluarsa]);

            // 3. Konfigurasi dan Kirim Email
            // $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.example.com'; // <<< GANTI SMTP HOST
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@example.com'; // <<< GANTI EMAIL
            $mail->Password = 'your_password';       // <<< GANTI PASSWORD
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom('no-reply@pinrulab.com', 'Admin PinruLab');
            $mail->addAddress($email);
            $mail->isHTML(true);
            
            // Tautan diarahkan ke file reset_password_mahasiswa.php
            $reset_link = "http://yourwebsite.com/auth/reset_password_mahasiswa.php?token=" . $token . "&email=" . urlencode($email);

            $mail->Subject = 'Reset Password Akun Mahasiswa';
            $mail->Body    = 'Klik tautan ini untuk mereset password: <a href="' . $reset_link . '">Reset Password Saya</a>. Tautan ini kadaluarsa dalam 1 jam.';

            if ($mail->send()) {
                $response = "Tautan reset password telah dikirim ke **$email**. Silakan cek kotak masuk Anda.";
            } else {
                throw new Exception("Email gagal dikirim. Error: " . $mail->ErrorInfo);
            }

        } else {
            // Pesan ambigu untuk keamanan
            $response = "Jika email **$email** terdaftar, tautan reset akan segera dikirimkan.";
        }
    } catch (Exception $e) {
        $response = "Terjadi kesalahan sistem. Mohon coba lagi nanti.";
    }
    
    // Redirect kembali ke form lupa password mahasiswa
    header("Location: lupa_password_mahasiswa.php?message=" . urlencode($response));
    exit();

} else {
    // Jika diakses langsung
    header("Location: lupa_password_mahasiswa.php");
    exit();
}
?>