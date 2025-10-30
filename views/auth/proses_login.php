<?php
// 1. Mulai session
session_start();

// 2. Hubungkan ke database
// Memanggil file koneksi Anda
require '../../settings/koneksi.php'; 

// 3. Buat objek database & ambil koneksinya
// Ini adalah langkah kunci yang hilang sebelumnya
$database = new Database(); // Membuat objek dari class Database
$conn = $database->conn;    // Mengambil variabel koneksi ($conn) dari dalam class

// 4. Pastikan form di-submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 5. Ambil data dari form
    $login_input = $_POST['username']; 
    $password_input = $_POST['password'];

    // Ganti 'users' dengan nama tabel Anda jika berbeda
    $nama_tabel = 'users'; 

    try {
        // 6. SQL Query (dengan placeholder '?')
        // Ini sesuai dengan placeholder form Anda (NIP/Email/Username)
        $sql = "SELECT * FROM $nama_tabel WHERE username = ? OR email = ?";
        
        // 7. Siapkan dan jalankan statement MySQLi
        $stmt = $conn->prepare($sql);
        
        // 'ss' berarti kita binding 2 variabel string
        $stmt->bind_param("ss", $login_input, $login_input); 
        
        $stmt->execute();
        
        // Ambil hasil query
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); // Ambil data user sebagai array

        // 8. Verifikasi user dan password
        // (Pastikan Anda menggunakan password_hash() di database, BUKAN MD5)
        if ($user && password_verify($password_input, $user['password'])) {
            
            // 9. JIKA BERHASIL: Simpan data penting ke session
            $_SESSION['logged_in'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['role'] = $user['role'];

            // 10. Logika Redirect Berdasarkan Role
            $role = $user['role'];

            if ($role == 'admin') {
                header("Location: ../admin/dashboard/dashboard.php");
                exit;
            } elseif ($role == 'pengelola') {
                header("Location: ../pengelola/dashboard.php");
                exit;
            } else {
                header("Location: login.php?error=Role tidak dikenali");
                exit;
            }

        } else {
            // 11. JIKA GAGAL: (User tidak ada atau password salah)
            header("Location: login.php?error=Username atau password salah");
            exit;
        }
        
        // Tutup statement
        $stmt->close();

    } catch (Exception $e) {
        // Tangani error database
        header("Location: login.php?error=Terjadi masalah sistem: " . $e->getMessage());
        exit;
    }
    
    // Tutup koneksi
    $conn->close();

} else {
    // Jika file diakses langsung, tendang ke login
    header("Location: login.php");
    exit;
}
?>