<?php
// Bagian Koneksi Database
require_once '../../settings/koneksi.php';

// --- LOGIKA UTAMA: MEMPROSES FORM SUBMIT (POST REQUEST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_new_password'])) {
    
    $token = $_POST['token'];
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'mahasiswa';
    
    // ... (Logika Validasi Password) ...
    $error_message = "";

    if ($new_password !== $confirm_password) {
        $error_message = "Password baru dan konfirmasi tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $error_message = "Password minimal harus 6 karakter.";
    } else {
        // Cek kembali token di tabel password_reset_temp
        $stmt_check = $pdo->prepare("SELECT 1 FROM password_reset_temp WHERE token = ? AND email = ? AND role = ?");
        $stmt_check->execute([$token, $email, $role]);

        if ($stmt_check->rowCount() > 0) {
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update Password di Tabel Mahasiswa
            $update_stmt = $pdo->prepare("UPDATE mahasiswa SET password = ? WHERE email = ?");
            
            if ($update_stmt->execute([$hashed_password, $email])) {
                
                // Hapus Token Reset
                $pdo->prepare("DELETE FROM password_reset_temp WHERE email = ?")->execute([$email]);

                $success_message = 'Password Anda berhasil diubah. Silakan Login.';
                header("Location: login_mahasiswa.php?message=" . urlencode($success_message));
                exit();
                
            } else {
                 $error_message = "Gagal menyimpan password baru ke database.";
            }
        } else {
             $error_message = "Tautan reset tidak valid atau sudah kadaluarsa.";
        }
    }
    
    // Jika ada kegagalan, kembalikan ke halaman login dengan pesan error
    header("Location: login_mahasiswa.php?error=" . urlencode($error_message));
    exit();
}


// --- LOGIKA UTAMA: MEMVERIFIKASI TOKEN (GET REQUEST DARI EMAIL) ---
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';
$current_time = date("Y-m-d H:i:s");
$reset_data = false;

if (!empty($token) && !empty($email)) {
    try {
        // Cek Token, Email, Role='mahasiswa', dan Waktu Kadaluarsa di tabel password_reset_temp
        $stmt = $pdo->prepare("SELECT role FROM password_reset_temp 
                              WHERE token = ? AND email = ? AND role = 'mahasiswa' AND expires_at > ?");
        $stmt->execute([$token, $email, $current_time]);
        $reset_data = $stmt->fetch();
    } catch (PDOException $e) {
        $reset_data = false;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<body>
    <div class="login-container">
        <?php if ($reset_data) : ?>
            <form action="reset_password_mahasiswa.php" method="POST" class="login-form">
                <h2>Set Password Baru Mahasiswa</h2>
                <p>Silakan masukkan password baru Anda.</p>

                <div class="input-group">
                    <label for="new_password">Password Baru</label>
                    <div class="input-with-icon">
                        <i class="fas fa-key"></i> 
                        <input type="password" id="new_password" name="new_password" required placeholder="Masukkan password baru">
                        <i class="fas fa-eye-slash toggle-password"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <div class="input-with-icon">
                        <i class="fas fa-lock"></i> 
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
                        <i class="fas fa-eye-slash toggle-password"></i>
                    </div>
                </div>

                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email); ?>">

                <button type="submit" name="submit_new_password" class="login-button">Ubah Password</button>
            </form>

        <?php else : ?>
            <div class="login-form">
                <h2>Gagal Reset Password</h2>
                <p>Tautan reset password ini tidak valid, sudah digunakan, atau sudah kadaluarsa.</p>
                <p>Silakan kembali ke <a href="lupa_password_mahasiswa.php">Halaman Lupa Password Mahasiswa</a> untuk meminta tautan baru.</p>
            </div>
        <?php endif; ?>
    </div>

    </body>
</html>