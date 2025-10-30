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
    // Ini perbaikan yang sangat penting!
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
        $user = $result->fetch_assoc(); // Ambil data mahasiswa jika ada

        // --- LOGIKA PENGECEKAN BARU (LEBIH DETAIL) ---

        // Cek #1: Apakah user-nya ditemukan?
        if (!$user) {
            // JIKA USER TIDAK DITEMUKAN SAMA SEKALI
            $stmt->close();
            $conn->close();
            header("Location: login_mahasiswa.php?error=UserTidakDitemukan");
            exit;
        }

        // Cek #2: User-nya ada, tapi apakah password-nya cocok?
        if (!password_verify($password_input, $user['password'])) {
            // JIKA PASSWORD-NYA SALAH
            $stmt->close();
            $conn->close();
            header("Location: login_mahasiswa.php?error=PasswordSalah");
            exit;
        }

        // --- JIKA LOLOS KEDUA CEK DI ATAS, BARU LOGIN ---
        
        // 10. JIKA BERHASIL: Simpan data penting ke session
        $_SESSION['logged_in'] = true;
        $_SESSION['id_user'] = $user['id_mahasiswa']; 
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = 'mahasiswa'; 

        // 11. Redirect ke dashboard mahasiswa
        $stmt->close();
        $conn->close();
        header("Location: ../mahasiswa/dashboard/dashboard.php");
        exit;

    } catch (Exception $e) {
        // Tangani error database
        header("Location: login_mahasiswa.php?error=TerjadiMasalahSistem");
        exit;
    }

} else {
    // Jika file diakses langsung
    header("Location: login_mahasiswa.php");
    exit;
}
?>