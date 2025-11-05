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
            $nama_fasilitas = $_POST['nama_fasilitas'];
            
            if (empty($nama_fasilitas)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama fasilitas wajib diisi.'
                ];
                header('Location: form_fasilitas.php');
                exit;
            }

            $stmt = $koneksi->prepare("INSERT INTO fasilitas (nama_fasilitas) VALUES (?)");
            $stmt->bind_param("s", $nama_fasilitas);
            
            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Fasilitas berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal menambahkan data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: fasilitas_lab.php');
            exit;
        }
    }
    
    // --- PROSES EDIT DATA (UPDATE) ---
    elseif ($aksi == 'edit') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_fasilitas = $_POST['id_fasilitas'];
            $nama_fasilitas = $_POST['nama_fasilitas'];

            if (empty($nama_fasilitas)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama fasilitas wajib diisi.'
                ];
                header('Location: form_fasilitas.php?id=' . $id_fasilitas);
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE fasilitas SET nama_fasilitas = ? WHERE id_fasilitas = ?");
            $stmt->bind_param("si", $nama_fasilitas, $id_fasilitas);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Fasilitas berhasil diperbarui.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal memperbarui data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: fasilitas_lab.php');
            exit;
        }
    }
    
    // --- PROSES HAPUS DATA (DELETE) ---
    elseif ($aksi == 'hapus') {
        if (isset($_GET['id'])) {
            $id_fasilitas = $_GET['id'];

            try {
                // Jika suatu saat fasilitas dipakai di tabel lain, tinggal aktifkan query pengecekan relasi di bawah.
                // Contoh (jika nanti tabel ruangan punya fasilitas_id):
                /*
                $stmt_check = $koneksi->prepare("SELECT COUNT(*) as jumlah FROM ruangan WHERE fasilitas_id = ?");
                $stmt_check->bind_param("i", $id_fasilitas);
                $stmt_check->execute();
                $row = $stmt_check->get_result()->fetch_assoc();
                $stmt_check->close();

                if ($row['jumlah'] > 0) {
                    $_SESSION['pesan'] = [
                        'tipe' => 'danger',
                        'isi' => "Tidak dapat menghapus! Fasilitas ini masih digunakan oleh {$row['jumlah']} data ruangan."
                    ];
                    header('Location: fasilitas.php');
                    exit;
                }
                */

                $stmt = $koneksi->prepare("DELETE FROM fasilitas WHERE id_fasilitas = ?");
                $stmt->bind_param("i", $id_fasilitas);
                
                if ($stmt->execute()) {
                    $_SESSION['pesan'] = [
                        'tipe' => 'success',
                        'isi' => 'Fasilitas berhasil dihapus.'
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
            header('Location: fasilitas_lab.php');
            exit;
        }
    }
}

// Jika tidak ada aksi valid
header('Location: fasilitas.php');
exit;
?>
