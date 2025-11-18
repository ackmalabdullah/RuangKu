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
            $nama_kategori = $_POST['nama_kategori'];
            
            // Validasi sederhana (pastikan field tidak kosong)
            if (empty($nama_kategori)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama kategori wajib diisi.'
                ];
                header('Location: form_kategori.php');
                exit;
            }

            // Gunakan prepared statement untuk keamanan
            $stmt = $koneksi->prepare("INSERT INTO kategori_ruangan (nama_kategori) VALUES (?)");
            $stmt->bind_param("s", $nama_kategori);
            
            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Kategori berhasil ditambahkan.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal menambahkan data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: kategori.php');
            exit;
        }
    }
    
    // --- PROSES EDIT DATA (UPDATE) ---
    elseif ($aksi == 'edit') {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil data dari form
            $id_kategori = $_POST['id_kategori'];
            $nama_kategori = $_POST['nama_kategori'];

            if (empty($nama_kategori)) {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Nama kategori wajib diisi.'
                ];
                header('Location: form_kategori.php?id=' . $id_kategori);
                exit;
            }

            $stmt = $koneksi->prepare("UPDATE kategori_ruangan SET nama_kategori = ? WHERE id_kategori = ?");
            $stmt->bind_param("si", $nama_kategori, $id_kategori);

            if ($stmt->execute()) {
                $_SESSION['pesan'] = [
                    'tipe' => 'success',
                    'isi' => 'Kategori ruangan berhasil diperbarui.'
                ];
            } else {
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Gagal memperbarui data: ' . $stmt->error
                ];
            }
            $stmt->close();
            header('Location: kategori.php');
            exit;
        }
    }
    
    // --- PROSES HAPUS DATA (DELETE) ---
    elseif ($aksi == 'hapus') {
        if (isset($_GET['id'])) {
            $id_kategori = $_GET['id'];
            
            // --- LOGIKA BARU DIMULAI DI SINI ---
            try {
                // 1. Cek dulu apakah ID kategori ini dipakai di tabel 'ruangan'
                // Asumsi berdasarkan error Anda: kolom di 'ruangan' adalah 'kategori_id'
                $stmt_check = $koneksi->prepare("SELECT COUNT(*) as jumlah FROM ruangan WHERE kategori_id = ?");
                $stmt_check->bind_param("i", $id_kategori);
                $stmt_check->execute();
                $result_check = $stmt_check->get_result();
                $row = $result_check->fetch_assoc();
                $jumlah_ruangan_terkait = $row['jumlah'];
                $stmt_check->close();

                // 2. Jika jumlah > 0 (ada relasi), JANGAN HAPUS. Beri pesan error.
                if ($jumlah_ruangan_terkait > 0) {
                    $_SESSION['pesan'] = [
                        'tipe' => 'danger',
                        'isi' => "Gagal menghapus! Kategori ini masih digunakan oleh $jumlah_ruangan_terkait ruangan."
                    ];
                } else {
                    // 3. Jika jumlah == 0 (aman), baru jalankan proses HAPUS.
                    $stmt = $koneksi->prepare("DELETE FROM kategori_ruangan WHERE id_kategori = ?");
                    $stmt->bind_param("i", $id_kategori);
                    
                    if ($stmt->execute()) {
                        $_SESSION['pesan'] = [
                            'tipe' => 'success',
                            'isi' => 'Kategori ruangan berhasil dihapus.'
                        ];
                    } else {
                        $_SESSION['pesan'] = [
                            'tipe' => 'danger',
                            'isi' => 'Gagal menghapus data: ' . $stmt->error
                        ];
                    }
                    $stmt->close();
                }

            } catch (mysqli_sql_exception $e) {
                // 4. Ini adalah blok cadangan jika terjadi error SQL lain
                $_SESSION['pesan'] = [
                    'tipe' => 'danger',
                    'isi' => 'Terjadi error database: ' . $e->getMessage()
                ];
            }
            // --- LOGIKA BARU SELESAI ---

            // Apapun hasilnya, redirect kembali ke halaman kategori
            header('Location: kategori.php');
            exit;
        }
    }
}

// Jika tidak ada aksi yang valid, kembalikan ke halaman utama
header('Location: kategori.php');
exit;
?>