<?php

session_start();

require '../../settings/koneksi.php'; 

$database = new Database();
$conn = $database->conn;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $login_input = trim($_POST['username']); 
    $password_input = trim($_POST['password']); 

    $nama_tabel = 'users'; 

    try {
        $sql = "SELECT * FROM $nama_tabel WHERE username = ? OR email = ?";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bind_param("ss", $login_input, $login_input); 
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        $user = $result->fetch_assoc(); 

        if (!$user) {
            $stmt->close();
            $conn->close();
            header("Location: login.php?error=Username atau Email tidak terdaftar.");
            exit;
        }
        if (!password_verify($password_input, $user['password'])) { 
            $stmt->close();
            $conn->close();
            header("Location: login.php?error=Password yang Anda masukkan salah.");
            exit;
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        $role = $user['role'];

        if ($role == 'admin') {
            header("Location: ../admin/dashboard/dashboard.php");
            exit;
        } elseif ($role == 'pengelola_ruangan') {
            header("Location: ../pengelola_ruangan/dashboard/dashboard.php");
            exit;
        } elseif ($role == 'pengelola_lab') {
            header("Location: ../pengelola_lab/dashboard/dashboard.php");
            exit;
        } elseif ($role == 'jurusan') {
            header("Location: ../jurusan/dashboard/dashboard.php");
            exit;
        } else {
            header("Location: login.php?error=Role tidak dikenali");
            exit;
        }

        $stmt->close();

    } catch (Exception $e) {
        header("Location: login.php?error=Terjadi masalah pada sistem. Silakan coba lagi nanti.");
        exit;
    }
    
    $conn->close();

} else {
    header("Location: login.php");
    exit;
}
?>