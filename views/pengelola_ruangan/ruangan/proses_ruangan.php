<?php
session_start();

require '../../../settings/koneksi.php';

// Instansiasi koneksi
$database = new Database();
$koneksi  = $database->conn;

// Cek apakah request valid
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['aksi'])) {
    $_SESSION['pesan'] = [
        'tipe' => 'warning',
        'isi'  => 'Akses tidak diizinkan.'
    ];
    header('Location: ruangan.php');
    exit();
}

// Folder penyimpanan gambar ruangan
$target_dir = '../../../assets/img/ruangan/';

// Pastikan folder ada
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Ambil aksi
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

switch ($aksi) {

    case 'tambah':

        // Sanitasi input
        $nama_ruangan   = trim($_POST['nama_ruangan'] ?? '');
        $kategori_id    = (int)($_POST['kategori_id'] ?? 0);
        $lokasi         = trim($_POST['lokasi'] ?? '');
        $kapasitas      = (int)($_POST['kapasitas'] ?? 0);
        $status_ruangan = trim($_POST['status_ruangan'] ?? 'tersedia');

        // Validasi wajib
        if (empty($nama_ruangan) || $kategori_id <= 0 || $kapasitas <= 0) {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Data tidak lengkap!'];
            header('Location: form_ruangan.php');
            exit();
        }

        // Proses upload gambar
        $upload_result = handleFileUpload('gambar', $target_dir, 'ruangan_');
        if ($upload_result['status'] === 'error') {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => $upload_result['message']];
            header('Location: form_ruangan.php');
            exit();
        }
        $nama_file_gambar = $upload_result['file_name']; // bisa kosong jika tidak upload

        // Escape string untuk query
        $nama_ruangan_esc   = mysqli_real_escape_string($koneksi, $nama_ruangan);
        $lokasi_esc         = mysqli_real_escape_string($koneksi, $lokasi);
        $status_ruangan_esc = mysqli_real_escape_string($koneksi, $status_ruangan);

        // Query insert ruangan
        $query_insert = "INSERT INTO ruangan 
            (kategori_id, nama_ruangan, lokasi, kapasitas, status_ruangan, gambar) 
            VALUES 
            ('$kategori_id', '$nama_ruangan_esc', '$lokasi_esc', '$kapasitas', '$status_ruangan_esc', '$nama_file_gambar')";

        if (mysqli_query($koneksi, $query_insert)) {
            $ruangan_id_baru = mysqli_insert_id($koneksi);

            // Simpan fasilitas ruangan
            simpanFasilitasRuangan($koneksi, $ruangan_id_baru, $_POST);

            $_SESSION['pesan'] = [
                'tipe' => 'success',
                'isi'  => "Ruangan <strong>$nama_ruangan</strong> berhasil ditambahkan!"
            ];
        } else {
            // Hapus gambar jika gagal insert
            if (!empty($nama_file_gambar)) {
                @unlink($target_dir . $nama_file_gambar);
            }
            $_SESSION['pesan'] = [
                'tipe' => 'error',
                'isi'  => 'Gagal menambah ruangan: ' . mysqli_error($koneksi)
            ];
        }
        break;


    case 'edit':

        $id_ruangan     = (int)($_POST['id_ruangan'] ?? 0);
        $nama_ruangan   = trim($_POST['nama_ruangan'] ?? '');
        $kategori_id    = (int)($_POST['kategori_id'] ?? 0);
        $lokasi         = trim($_POST['lokasi'] ?? '');
        $kapasitas      = (int)($_POST['kapasitas'] ?? 0);
        $status_ruangan = trim($_POST['status_ruangan'] ?? 'tersedia');
        $gambar_lama    = $_POST['gambar_lama'] ?? '';

        if ($id_ruangan <= 0 || empty($nama_ruangan)) {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'ID atau nama ruangan tidak valid!'];
            header('Location: ruangan.php');
            exit();
        }

        $gambar_baru = $gambar_lama; // default pakai yang lama

        // Jika ada upload gambar baru
        if (!empty($_FILES['gambar']['name'])) {
            $upload = handleFileUpload('gambar', $target_dir, 'ruangan_');
            if ($upload['status'] === 'error') {
                $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => $upload['message']];
                header("Location: form_ruangan.php?id=$id_ruangan");
                exit();
            }
            $gambar_baru = $upload['file_name'];
            // Hapus gambar lama
            if (!empty($gambar_lama) && file_exists($target_dir . $gambar_lama)) {
                @unlink($target_dir . $gambar_lama);
            }
        }

        // Escape
        $nama_esc   = mysqli_real_escape_string($koneksi, $nama_ruangan);
        $lokasi_esc = mysqli_real_escape_string($koneksi, $lokasi);
        $status_esc = mysqli_real_escape_string($koneksi, $status_ruangan);

        // Update ruangan
        $query_update = "UPDATE ruangan SET 
            kategori_id     = '$kategori_id',
            nama_ruangan    = '$nama_esc',
            lokasi          = '$lokasi_esc',
            kapasitas       = '$kapasitas',
            status_ruangan  = '$status_esc',
            gambar          = '$gambar_baru'
            WHERE id_ruangan = '$id_ruangan'";

        if (mysqli_query($koneksi, $query_update)) {
            simpanFasilitasRuangan($koneksi, $id_ruangan, $_POST);
            $_SESSION['pesan'] = [
                'tipe' => 'success',
                'isi'  => "Ruangan <strong>$nama_ruangan</strong> berhasil diperbarui!"
            ];
        } else {
            // Rollback gambar baru jika gagal
            if ($gambar_baru != $gambar_lama && !empty($gambar_baru)) {
                @unlink($target_dir . $gambar_baru);
            }
            $_SESSION['pesan'] = [
                'tipe' => 'error',
                'isi'  => 'Gagal update ruangan: ' . mysqli_error($koneksi)
            ];
        }
        break;


    case 'hapus':

        $id_ruangan = (int)($_GET['id'] ?? 0);
        if ($id_ruangan <= 0) {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'ID tidak valid!'];
            header('Location: ruangan.php');
            exit();
        }

        // Ambil nama gambar untuk dihapus
        $q = mysqli_query($koneksi, "SELECT gambar FROM ruangan WHERE id_ruangan = '$id_ruangan'");
        $data = mysqli_fetch_assoc($q);
        $gambar = $data['gambar'] ?? '';

        // Hapus relasi fasilitas dulu
        mysqli_query($koneksi, "DELETE FROM ruangan_fasilitas WHERE ruangan_id = '$id_ruangan'");

        // Hapus ruangan
        if (mysqli_query($koneksi, "DELETE FROM ruangan WHERE id_ruangan = '$id_ruangan'")) {
            if (!empty($gambar) && file_exists($target_dir . $gambar)) {
                @unlink($target_dir . $gambar);
            }
            $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Ruangan berhasil dihapus.'];
        } else {
            $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal hapus ruangan: ' . mysqli_error($koneksi)];
        }
        break;


    default:
        $_SESSION['pesan'] = ['tipe' => 'warning', 'isi' => 'Aksi tidak dikenali.'];
        break;
}

// Redirect kembali ke halaman utama ruangan
header('Location: ruangan.php');
exit();


/**
 * Upload file gambar dengan validasi lengkap
 */
function handleFileUpload($input_name, $target_dir, $prefix = 'img_') {
    if (empty($_FILES[$input_name]['name'])) {
        return ['status' => 'success', 'file_name' => ''];
    }

    $file      = $_FILES[$input_name];
    $orig_name = basename($file['name']);
    $tmp_name  = $file['tmp_name'];
    $size      = $file['size'];
    $error     = $file['error'];
    $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    // Validasi ekstensi
    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($ext, $allowed)) {
        return ['status' => 'error', 'message' => 'Format file harus JPG, JPEG, atau PNG!'];
    }

    // Validasi ukuran (maks 5MB)
    if ($size > 5 * 1024 * 1024) {
        return ['status' => 'error', 'message' => 'Ukuran file maksimal 5MB!'];
    }

    // Validasi error upload
    if ($error !== UPLOAD_ERR_OK) {
        return ['status' => 'error', 'message' => 'Terjadi error saat upload file.'];
    }

    // Buat nama unik
    $new_name = uniqid($prefix, true) . '.' . $ext;
    $destination = $target_dir . $new_name;

    if (move_uploaded_file($tmp_name, $destination)) {
        return ['status' => 'success', 'file_name' => $new_name];
    } else {
        return ['status' => 'error', 'message' => 'Gagal menyimpan file ke server.'];
    }
}

/**
 * Simpan relasi fasilitas ruangan (hapus lama, insert baru)
 */
function simpanFasilitasRuangan($koneksi, $ruangan_id, $post_data) {
    // Hapus semua relasi lama
    $delete_query = "DELETE FROM ruangan_fasilitas WHERE ruangan_id = '" . (int)$ruangan_id . "'";
    mysqli_query($koneksi, $delete_query);

    // Jika tidak ada fasilitas yang dipilih, langsung return
    if (!isset($post_data['fasilitas_id']) || !is_array($post_data['fasilitas_id'])) {
        return;
    }

    $values = [];
    foreach ($post_data['fasilitas_id'] as $fasilitas_id) {
        $fasilitas_id = (int)$fasilitas_id;
        if ($fasilitas_id <= 0) continue;

        // Ambil jumlah (default 1)
        $jumlah = 1;
        if (isset($post_data['jumlah_fasilitas'][$fasilitas_id]) && is_numeric($post_data['jumlah_fasilitas'][$fasilitas_id])) {
            $jumlah = max(1, (int)$post_data['jumlah_fasilitas'][$fasilitas_id]);
        }

        $values[] = "('$ruangan_id', '$fasilitas_id', '$jumlah')";
    }

    // Insert baru jika ada
    if (!empty($values)) {
        $insert_query = "INSERT INTO ruangan_fasilitas (ruangan_id, fasilitas_id, jumlah) VALUES " . implode(', ', $values);
        mysqli_query($koneksi, $insert_query);
    }
}
?>