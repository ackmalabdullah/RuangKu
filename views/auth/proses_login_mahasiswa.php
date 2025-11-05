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

    // 5. Ambil data dari form DAN HAPUS SPASI di awal/akhir
    $login_input = trim($_POST['email']); 
    $password_input = trim($_POST['password']); 

    // 6. Tentukan nama tabel
    $nama_tabel = 'mahasiswa';

    try {
        // 7. SQL Query: Cari berdasarkan NIM atau Email
        $sql = "SELECT * FROM $nama_tabel WHERE nim = ? OR email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $login_input, $login_input); 
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Cek #1: Apakah user-nya ditemukan?
        if (!$user) {
            $stmt->close();
            $conn->close();
            // --- PERUBAHAN PESAN ERROR ---
            header("Location: login_mahasiswa.php?error=NIM atau Email tidak terdaftar.");
            exit;
        }

        // Cek #2: User-nya ada, tapi apakah password-nya cocok?
        if (!password_verify($password_input, $user['password'])) {
            $stmt->close();
            $conn->close();
            // --- PERUBAHAN PESAN ERROR ---
            header("Location: login_mahasiswa.php?error=Password yang Anda masukkan salah.");
            exit;
        }

        // --- JIKA LOLOS KEDUA CEK DI ATAS, BARU LOGIN ---
        
        // 10. JIKA BERHASIL: Simpan data penting ke session
        $_SESSION['logged_in'] = true;
        
        // ==========================================================
        // PERUBAHAN DI SINI: Key 'id_user' diubah menjadi 'id_mahasiswa'
        $_SESSION['id_mahasiswa'] = $user['id_mahasiswa']; 
        // ==========================================================
        
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = 'mahasiswa'; 

        // 11. Redirect ke dashboard mahasiswa
        $stmt->close();
        $conn->close();
        // (Pastikan path ini benar)
        header("Location: ../mahasiswa/dashboard/dashboard.php"); 
        exit;

    } catch (Exception $e) {
        // Tangani error database
        // --- PERUBAHAN PESAN ERROR ---
        header("Location: login_mahasiswa.php?error=Terjadi masalah pada sistem. Silakan coba lagi nanti.");
        exit;
    }

} else {
    // Jika file diakses langsung
    header("Location: login_mahasiswa.php");
    exit;
}
?>