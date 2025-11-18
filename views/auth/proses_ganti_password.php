<?php
// Pastikan path ke file koneksi Anda sudah benar
// Asumsi: require '../../settings/koneksi.php'; adalah path yang benar ke file koneksi.php
require '../../settings/koneksi.php'; 
session_start(); 

// --- PERBAIKAN KRUSIAL DI SINI ---
// File koneksi.php Anda mendefinisikan Class Database.
// Untuk mengakses koneksi ($conn) sebagai $koneksi, kita harus instansiasi Class tersebut.
// Catatan: Jika koneksi.php Anda sudah di-require/di-include di file header, 
// Anda mungkin tidak perlu baris ini, tetapi ini mengatasi error "Undefined variable $koneksi".
try {
    $db_instance = new Database();
    $koneksi = $db_instance->conn; 
} catch (Exception $e) {
    // Tangani error jika instansiasi gagal
    $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal membuat koneksi database.'];
    header("Location: ../mahasiswa/profile/profile.php");
    exit;
}
// ------------------------------------

// Cek hak akses dan ketersediaan ID Mahasiswa di session
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'mahasiswa' || !isset($_SESSION['id_mahasiswa'])) {
    header("Location: ../../auth/login.php");
    exit;
}

if (isset($_POST['simpan_password'])) {
    
    // Ambil data POST
    $id_mahasiswa = $_POST['id_mahasiswa'];
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // VALIDASI KRITIS: Cek apakah ID dari form cocok dengan ID di session (Penting!)
    if ($id_mahasiswa != $_SESSION['id_mahasiswa']) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Akses ditolak. ID tidak valid.'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }

    // 1. Ambil Hashed Password dari Database
    // Variabel $koneksi sudah ada sekarang
    $stmt = $koneksi->prepare("SELECT password FROM mahasiswa WHERE id_mahasiswa = ?");
    
    if ($stmt === false) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Kesalahan prepare statement (SELECT).'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }
    
    $stmt->bind_param("i", $id_mahasiswa);
    $stmt->execute();
    $result = $stmt->get_result();
    $data_mhs = $result->fetch_assoc();
    $stmt->close();

    if (!$data_mhs) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Data pengguna tidak ditemukan.'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }

    $hashed_password_db = $data_mhs['password'];

    // 2. Verifikasi Password Lama
    if (!password_verify($password_lama, $hashed_password_db)) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Password lama yang Anda masukkan salah.'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }

    // 3. Validasi Password Baru
    if ($password_baru != $konfirmasi_password) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Konfirmasi Password Baru tidak cocok.'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }
    
    // Cek minimal panjang password
    if (strlen($password_baru) < 6) { 
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Password baru minimal harus 6 karakter.'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }

    // 4. Hash Password Baru
    $password_baru_hashed = password_hash($password_baru, PASSWORD_DEFAULT); 

    // 5. Update Password ke Database
    $query_update = "UPDATE mahasiswa SET password = ? WHERE id_mahasiswa = ?";
    $stmt_update = $koneksi->prepare($query_update);
    
    if ($stmt_update === false) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Kesalahan prepare statement (UPDATE).'];
        header("Location: ../mahasiswa/profile/profile.php");
        exit;
    }
    
    $stmt_update->bind_param("si", $password_baru_hashed, $id_mahasiswa);

    if ($stmt_update->execute()) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Password berhasil diubah.'];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal mengubah password: ' . $stmt_update->error];
    }
    
    $stmt_update->close();
    
    // Redirect kembali ke halaman profil
    header("Location: ../mahasiswa/profile/profile.php");
    exit;
} else {
    // Jika diakses tanpa POST simpan_password
    header("Location: ../mahasiswa/profile/profile.php");
    exit;
}
?>