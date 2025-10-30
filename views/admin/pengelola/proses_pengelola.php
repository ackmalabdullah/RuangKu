<?php
// Mulai session untuk menyimpan pesan feedback
session_start();

// Muat koneksi database
require '../../../settings/koneksi.php'; 

$database = new Database();
$koneksi = $database->conn;

// Cek apakah ada aksi yang diminta
if (isset($_GET['aksi'])) {
    
    $aksi = $_GET['aksi'];

    // --- PROSES TAMBAH DATA (CREATE) ---
    if ($aksi == 'tambah') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil data dari form
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $password = $_POST['password'];
            $role = $_POST['role'];
            
            // Validasi sederhana (pastikan password diisi)
            if (empty($nama) || empty($email) || empty($username) || empty($password) || empty($role)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Semua field wajib diisi.'
                ];
                header('Location: form_pengelola.php');
                exit;
            }

            // Hash password sebelum disimpan
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Gunakan prepared statement untuk keamanan
            $stmt = $koneksi->prepare("INSERT INTO users (nama, email, username, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nama, $email, $username, $hashed_password, $role);
            
            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Data pengelola berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal menambahkan data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: pengelola.php');
            exit;
        }
    }
    
    // --- PROSES EDIT DATA (UPDATE) ---
    elseif ($aksi == 'edit') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil data dari form
            $id_user = $_POST['id_user'];
            $nama = $_POST['nama'];
            $email = $_POST['email'];
            $username = $_POST['username'];
            $role = $_POST['role'];
            $password_baru = $_POST['password'];

            // Cek apakah user mengisi password baru
            if (!empty($password_baru)) {
                // Jika ya, hash password baru dan update
                $hashed_password = password_hash($password_baru, PASSWORD_DEFAULT);
                $stmt = $koneksi->prepare("UPDATE users SET nama = ?, email = ?, username = ?, password = ?, role = ? WHERE id_user = ?");
                $stmt->bind_param("sssssi", $nama, $email, $username, $hashed_password, $role, $id_user);
            } else {
                // Jika tidak, update data selain password
                $stmt = $koneksi->prepare("UPDATE users SET nama = ?, email = ?, username = ?, role = ? WHERE id_user = ?");
                $stmt->bind_param("ssssi", $nama, $email, $username, $role, $id_user);
            }

            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Data pengelola berhasil diperbarui.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal memperbarui data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: pengelola.php');
            exit;
        }
    }
    
    // --- PROSES HAPUS DATA (DELETE) ---
    elseif ($aksi == 'hapus') {
        // Aksi hapus biasanya via GET dari link
        if (isset($_GET['id'])) {
            $id_user = $_GET['id'];
            
            $stmt = $koneksi->prepare("DELETE FROM users WHERE id_user = ?");
            $stmt->bind_param("i", $id_user);
            
            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Data pengelola berhasil dihapus.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal menghapus data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: pengelola.php');
            exit;
        }
    }
}

// Jika tidak ada aksi yang valid, kembalikan ke halaman utama
header('Location: pengelola.php');
exit;
?>