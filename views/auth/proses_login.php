<?php
// 1. Mulai session
session_start();

// 2. Hubungkan ke database
require '../../settings/koneksi.php'; 

// 3. Buat objek database & ambil koneksinya
$database = new Database();
$conn = $database->conn;

// 4. Pastikan form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 5. Ambil data dari form dan hapus spasi
    $login_input = trim($_POST['username']); 
    $password_input = trim($_POST['password']); // Menggunakan trim() juga

    $nama_tabel = 'users'; 

    try {
        // 6. SQL Query (Sudah benar, TANPA NIP)
        $sql = "SELECT * FROM $nama_tabel WHERE username = ? OR email = ?";
        
        $stmt = $conn->prepare($sql);
        
        // 'ss' berarti kita binding 2 variabel string
        $stmt->bind_param("ss", $login_input, $login_input); 
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); 

        // --- INI LOGIKA BARU YANG ANDA INGINKAN ---

        // 8. Cek #1: Apakah user-nya ditemukan?
        if (!$user) {
            $stmt->close();
            $conn->close();
            // Pesan error jika user tidak ada
            header("Location: login.php?error=Username atau Email tidak terdaftar.");
            exit;
        }

        // 9. Cek #2: User ada, TAPI apakah password-nya cocok?
        // (Pastikan nama kolom Anda 'password')
        if (!password_verify($password_input, $user['password'])) { 
            $stmt->close();
            $conn->close();
            // Pesan error HANYA jika password salah
            header("Location: login.php?error=Password yang Anda masukkan salah.");
            exit;
        }

        // --- JIKA LOLOS KEDUA CEK, BARU LOGIN ---

        // 10. JIKA BERHASIL: Simpan data penting ke session
        $_SESSION['logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        // 11. Logika Redirect Berdasarkan Role
        $role = $user['role'];

        if ($role == 'admin') {
            header("Location: ../admin/dashboard/dashboard.php");
            exit;
        } elseif ($role == 'pengelola_ruangan') {
            header("Location: ../pengelola_ruangan/dashboard/dashboard.php");
            exit;
        } elseif ($role == 'pengelola_lab') {
            // --- PERBAIKAN TYPO DI SINI ---
            header("Location: ../pengelola_lab/dashboard/dashboard.php");
            exit;
        } else {
            header("Location: login.php?error=Role tidak dikenali");
            exit;
        }
        // (Blok 'else' yang lama sudah dihapus karena logikanya dipecah)

        $stmt->close();

    } catch (Exception $e) {
        // Tangani error database (Lebih aman disembunyikan dari user)
        header("Location: login.php?error=Terjadi masalah pada sistem. Silakan coba lagi nanti.");
        // (Menghapus $e->getMessage() agar lebih aman)
        exit;
    }
    
    $conn->close();

} else {
    // Jika file diakses langsung, tendang ke login
    header("Location: login.php");
    exit;
}
?>