<?php
session_start();
require '../../settings/koneksi.php'; 

$database = new Database();
$conn = $database->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 5. Ambil data dari form (TERMASUK YANG BARU)
    $nama = trim($_POST['nama']);
    $prodi_id = trim($_POST['prodi_id']); // BARU
    $nim = trim($_POST['nim']);
    $angkatan = trim($_POST['angkatan']); // BARU
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $konfirmasi_password = trim($_POST['konfirmasi_password']);

    // --- VALIDASI SISI SERVER ---
    if (empty($nama) || empty($prodi_id) || empty($nim) || empty($angkatan) || empty($email) || empty($password) || empty($konfirmasi_password)) {
        header("Location: register.php?error=Semua field wajib diisi.");
        exit;
    }
    if ($password !== $konfirmasi_password) {
        header("Location: register.php?error=Password dan konfirmasi password tidak cocok.");
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=Format email tidak valid.");
        exit;
    }
    if (!is_numeric($prodi_id)) {
        header("Location: register.php?error=Data prodi tidak valid.");
        exit;
    }
    if (!is_numeric($angkatan) || strlen($angkatan) != 4) {
        header("Location: register.php?error=Format tahun angkatan tidak valid (Contoh: 2024).");
        exit;
    }

    // --- PENGECEKAN DATABASE ---
    try {
        // Cek duplikat NIM atau Email (Logika tidak berubah)
        $sql_check = "SELECT nim, email FROM mahasiswa WHERE nim = ? OR email = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $nim, $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $existing_user = $result_check->fetch_assoc();
            if ($existing_user['nim'] == $nim) {
                header("Location: register.php?error=NIM sudah terdaftar.");
            } else if ($existing_user['email'] == $email) {
                header("Location: register.php?error=Email sudah terdaftar.");
            }
            $stmt_check->close();
            $conn->close();
            exit;
        }
        $stmt_check->close();

        // --- JIKA AMAN, LANJUTKAN ---

        // 7. HASH PASSWORD (Logika tidak berubah)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // 8. SQL Query untuk INSERT data (DISESUAIKAN)
        $sql_insert = "INSERT INTO mahasiswa (nama, prodi_id, nim, angkatan, email, password) VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_insert = $conn->prepare($sql_insert);
        // 'sisiss' -> string, integer, string, integer, string, string
        // (Asumsi prodi_id dan angkatan adalah integer)
        $stmt_insert->bind_param("sisiss", $nama, $prodi_id, $nim, $angkatan, $email, $hashed_password);

        // 9. Eksekusi dan redirect (Logika tidak berubah)
        if ($stmt_insert->execute()) {
            header("Location: login_mahasiswa.php?message=Registrasi berhasil! Silakan login.");
        } else {
            header("Location: register.php?error=Terjadi kesalahan saat mendaftar.");
        }

        $stmt_insert->close();
        $conn->close();
        exit;

    } catch (Exception $e) {
        header("Location: register.php?error=Terjadi masalah pada sistem. Silakan coba lagi nanti.");
        exit;
    }

} else {
    header("Location: register.php");
    exit;
}
?>