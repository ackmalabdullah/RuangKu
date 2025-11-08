<?php
session_start();

// Asumsi file koneksi ada di path yang benar dan mendefinisikan kelas Database
require '../../../settings/koneksi.php'; 

// --- INSTANSIASI OBJEK DATABASE DAN AMBIL KONEKSI ---
// Membuat objek dari kelas Database
$database = new Database(); 
// Mengambil koneksi mysqli dari properti $conn dalam objek
$koneksi = $database->conn; 

// Cek apakah request datang dari POST, jika tidak, redirect
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['aksi'])) {
    header('Location: ruangan.php');
    exit;
}

// Lokasi Folder Gambar (Sesuaikan path ini jika perlu)
$target_dir = '../../../assets/img/ruangan/'; 

$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? ''; // Ambil aksi dari POST atau GET

switch ($aksi) {
    
    // --- AKSI TAMBAH DATA RUANGAN ---
    case 'tambah':
        // Ambil data ruangan utama
        // **PERHATIAN:** Gunakan $koneksi untuk sanitasi input
        $nama_ruangan = mysqli_real_escape_string($koneksi, $_POST['nama_ruangan']);
        $kategori_id = mysqli_real_escape_string($koneksi, $_POST['kategori_id']);
        $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $kapasitas = (int)$_POST['kapasitas'];
        $status_ruangan = mysqli_real_escape_string($koneksi, $_POST['status_ruangan']);
        
        $gambar_file_name = '';

        // 1. Proses Upload Gambar
        $upload_result = handleFileUpload('gambar', $target_dir);
        if ($upload_result['status'] === 'error') {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => $upload_result['message']];
            header('Location: form_ruangan.php');
            exit;
        }
        $gambar_file_name = $upload_result['file_name'];
        
        // 2. Insert data ruangan utama
        $insert_query = "INSERT INTO ruangan (nama_ruangan, kategori_id, lokasi, kapasitas, status_ruangan, gambar) 
                         VALUES ('$nama_ruangan', '$kategori_id', '$lokasi', '$kapasitas', '$status_ruangan', '$gambar_file_name')";

        if (mysqli_query($koneksi, $insert_query)) {
            $ruangan_id_baru = mysqli_insert_id($koneksi);
            
            // 3. Simpan relasi Fasilitas
            simpanFasilitasRelasi($koneksi, $ruangan_id_baru, $_POST);
            
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data ruangan **' . $nama_ruangan . '** berhasil ditambahkan.'];
        } else {
            // Hapus file yang sudah terlanjur diupload jika query gagal
            if (!empty($gambar_file_name)) {
                @unlink($target_dir . $gambar_file_name);
            }
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal menambahkan data ruangan: ' . mysqli_error($koneksi)];
        }
        break;

    // --- AKSI EDIT DATA RUANGAN ---
    case 'edit':
        $id_ruangan = mysqli_real_escape_string($koneksi, $_POST['id_ruangan']);
        $nama_ruangan = mysqli_real_escape_string($koneksi, $_POST['nama_ruangan']);
        $kategori_id = mysqli_real_escape_string($koneksi, $_POST['kategori_id']);
        $lokasi = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
        $kapasitas = (int)$_POST['kapasitas'];
        $status_ruangan = mysqli_real_escape_string($koneksi, $_POST['status_ruangan']);
        $gambar_lama = mysqli_real_escape_string($koneksi, $_POST['gambar_lama']);
        $gambar_update = $gambar_lama; // Default menggunakan gambar lama

        // 1. Proses Upload Gambar (Jika ada file baru)
        if (!empty($_FILES['gambar']['name'])) {
            $upload_result = handleFileUpload('gambar', $target_dir);
            
            if ($upload_result['status'] === 'error') {
                $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => $upload_result['message']];
                header('Location: form_ruangan.php?id=' . $id_ruangan);
                exit;
            }
            // Jika upload berhasil, set nama file baru dan hapus gambar lama
            $gambar_update = $upload_result['file_name'];
            if (!empty($gambar_lama)) {
                @unlink($target_dir . $gambar_lama);
            }
        }
        
        // 2. Update data ruangan utama
        $update_query = "UPDATE ruangan SET 
                            nama_ruangan = '$nama_ruangan', 
                            kategori_id = '$kategori_id', 
                            lokasi = '$lokasi', 
                            kapasitas = '$kapasitas', 
                            status_ruangan = '$status_ruangan',
                            gambar = '$gambar_update'
                        WHERE id_ruangan = '$id_ruangan'";

        if (mysqli_query($koneksi, $update_query)) {
            
            // 3. Simpan relasi Fasilitas (Hapus dan Insert ulang)
            simpanFasilitasRelasi($koneksi, $id_ruangan, $_POST);

            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data ruangan **' . $nama_ruangan . '** berhasil diubah.'];
        } else {
            // Hapus file baru yang terlanjur diupload jika query gagal (jika $gambar_update != $gambar_lama)
            if ($gambar_update != $gambar_lama && !empty($gambar_update)) {
                @unlink($target_dir . $gambar_update);
            }
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal mengubah data ruangan: ' . mysqli_error($koneksi)];
        }
        break;
        
    // --- AKSI HAPUS DATA RUANGAN ---
    case 'hapus':
        $id_ruangan = mysqli_real_escape_string($koneksi, $_GET['id']);
        
        // 1. Ambil nama gambar untuk dihapus
        $result_gambar = mysqli_query($koneksi, "SELECT gambar FROM ruangan WHERE id_ruangan = '$id_ruangan'");
        $data_gambar = mysqli_fetch_assoc($result_gambar);
        $gambar_lama = $data_gambar['gambar'] ?? null;
        
        // 2. Hapus relasi fasilitas terlebih dahulu
        $query_del_fas = "DELETE FROM ruangan_fasilitas WHERE ruangan_id = '$id_ruangan'";
        mysqli_query($koneksi, $query_del_fas);
        
        // 3. Hapus data ruangan utama
        $query_del_ruang = "DELETE FROM ruangan WHERE id_ruangan = '$id_ruangan'";
        
        if (mysqli_query($koneksi, $query_del_ruang)) {
            // 4. Hapus file gambar fisik dari server
            if (!empty($gambar_lama)) {
                @unlink($target_dir . $gambar_lama);
            }
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data ruangan berhasil dihapus.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal menghapus data ruangan: ' . mysqli_error($koneksi)];
        }
        break;

    default:
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Aksi tidak valid.'];
        break;
}

// Redirect ke halaman daftar ruangan
header('Location: ruangan.php');
exit;


// -------------------------------------------------------------
// FUNGSI BANTUAN
// -------------------------------------------------------------

/**
 * Mengelola proses upload file gambar.
 * @param string $input_name Nama input file dari form ($_FILES)
 * @param string $target_dir Folder tujuan penyimpanan
 * @return array Hasil upload (status, message, file_name)
 */
function handleFileUpload($input_name, $target_dir) {
    if (empty($_FILES[$input_name]['name'])) {
        return ['status' => 'success', 'message' => 'Tidak ada file diupload.', 'file_name' => ''];
    }

    $file = $_FILES[$input_name];
    $file_name = basename($file['name']);
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    $allowed_ext = ['jpg', 'jpeg', 'png'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!in_array($file_ext, $allowed_ext)) {
        return ['status' => 'error', 'message' => 'Hanya file JPG, JPEG, & PNG yang diizinkan.'];
    }
    if ($file_size > $max_size) {
        return ['status' => 'error', 'message' => 'Ukuran file terlalu besar (Maks. 5MB).'];
    }
    if ($file_error !== 0) {
        return ['status' => 'error', 'message' => 'Terjadi kesalahan saat mengupload file.'];
    }

    // Buat nama file unik
    $new_file_name = uniqid('room_', true) . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $target_file)) {
        return ['status' => 'success', 'message' => 'File berhasil diupload.', 'file_name' => $new_file_name];
    } else {
        return ['status' => 'error', 'message' => 'Gagal memindahkan file yang diupload.'];
    }
}

/**
 * Menyimpan relasi fasilitas ke tabel ruangan_fasilitas (menghapus yang lama jika ada dan insert yang baru).
 * @param mysqli $koneksi Koneksi database
 * @param int $ruangan_id ID ruangan yang sedang diproses
 * @param array $post_data Data POST dari form
 */
function simpanFasilitasRelasi($koneksi, $ruangan_id, $post_data) {
    
    $fasilitas_terpilih = $post_data['fasilitas_id'] ?? [];
    $jumlah_fasilitas = $post_data['jumlah_fasilitas'] ?? [];

    // Hapus semua relasi lama terlebih dahulu
    $hapus_relasi_lama = "DELETE FROM ruangan_fasilitas WHERE ruangan_id = '" . mysqli_real_escape_string($koneksi, $ruangan_id) . "'";
    mysqli_query($koneksi, $hapus_relasi_lama);

    // Masukkan relasi fasilitas yang baru
    if (!empty($fasilitas_terpilih)) {
        $insert_values = [];
        foreach ($fasilitas_terpilih as $fasilitas_id) {
            $id = mysqli_real_escape_string($koneksi, $fasilitas_id);
            
            // Ambil jumlah dari array asosiatif (mengabaikan input disabled, hanya mengambil yang dicentang)
            $jumlah = isset($jumlah_fasilitas[$fasilitas_id]) && is_numeric($jumlah_fasilitas[$fasilitas_id]) 
                        ? (int)$jumlah_fasilitas[$fasilitas_id] 
                        : 1;
            
            // Pastikan jumlah minimal 1
            if ($jumlah < 1) $jumlah = 1;

            $insert_values[] = "('" . mysqli_real_escape_string($koneksi, $ruangan_id) . "', '" . $id . "', '" . $jumlah . "')";
        }

        if (!empty($insert_values)) {
            $insert_relasi = "INSERT INTO ruangan_fasilitas (ruangan_id, fasilitas_id, jumlah) VALUES " . implode(", ", $insert_values);
            mysqli_query($koneksi, $insert_relasi);
        }
    }
}

?>