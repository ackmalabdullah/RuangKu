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
            $nama_prodi = $_POST['nama_prodi'];
            
            if (empty($nama_prodi)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama prodi wajib diisi.'
                ];
                header('Location: form_prodi.php');
                exit;
            }

            $stmt = $koneksi->prepare("INSERT INTO prodi (nama_prodi) VALUES (?)");
            $stmt->bind_param("s", $nama_prodi);
            
            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Program studi berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal menambahkan data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: prodi.php');
            exit;
        }
    }
    
    // --- PROSES EDIT DATA (UPDATE) ---
    elseif ($aksi == 'edit') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_prodi = $_POST['id_prodi'];
            $nama_prodi = $_POST['nama_prodi'];

            if (empty($nama_prodi)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama prodi wajib diisi.'
                ];
                header('Location: form_prodi.php?id=' . $id_prodi);
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE prodi SET nama_prodi = ? WHERE id_prodi = ?");
            $stmt->bind_param("si", $nama_prodi, $id_prodi);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Program studi berhasil diperbarui.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal memperbarui data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: prodi.php');
            exit;
        }
    }
    
    // --- PROSES HAPUS DATA (DELETE) ---
    elseif ($aksi == 'hapus') {
        if (isset($_GET['id'])) {
            $id_prodi = $_GET['id'];

            try {
                // Jika nanti prodi terhubung dengan tabel lain, bisa tambahkan pengecekan relasi di sini
                $stmt = $koneksi->prepare("DELETE FROM prodi WHERE id_prodi = ?");
                $stmt->bind_param("i", $id_prodi);
                
                if ($stmt->execute()) {
                    $_SESSION['pesan'] = [
                        'tipe' => 'success',
                        'isi' => 'Program studi berhasil dihapus.'
                    ];
                } else {
                    $_SESSION['pesan'] = [
                        'tipe' => 'danger',
                        'isi' => 'Gagal menghapus data: ' . $stmt->error
                    ];
                }
                $stmt->close();

            } catch (mysqli_sql_exception $e) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Terjadi kesalahan database: ' . $e->getMessage()
                ];
            }
            header('Location: prodi.php');
            exit;
        }
    }
}

// Jika tidak ada aksi valid
header('Location: prodi.php');
exit;
?>