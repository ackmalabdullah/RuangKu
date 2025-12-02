<?php
session_start();

require __DIR__ . '/../../../settings/koneksi.php';
$database = new Database();
$conn = $database->conn;

$aksi = $_GET['aksi'] ?? '';
$id = $_GET['id'] ?? '';

$folder = __DIR__ . '/../../../assets/img/avatars/';
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// =============== TAMBAH ===============
if ($aksi == "tambah") {

    $prodi_id = $_POST['prodi_id'];
    $nim      = $_POST['nim'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $password = $_POST['password'];
    $angkatan = $_POST['angkatan'];

    if (empty($prodi_id) || empty($nim) || empty($nama) || empty($email) || empty($password) || empty($angkatan)) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Semua field wajib diisi.'];
        header("Location: mahasiswa.php");
        exit;
    }

    // Upload foto
    $foto = null;
    $foto = $_FILES['foto']['name'];

    // Jika tidak upload foto, gunakan foto default
    if (empty($foto)) {
        $foto = 'null.jpg';
    } else {
        $tmp = $_FILES['foto']['tmp_name'];
        $folder = __DIR__ . '/../../../assets/img/avatars/';

        move_uploaded_file($tmp, $folder . $foto);
    }


    // Simpan mahasiswa
    $stmt = $conn->prepare("
        INSERT INTO mahasiswa (prodi_id, nim, nama, email, password, angkatan, foto)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("issssis", $prodi_id, $nim, $nama, $email, $password_hash, $angkatan, $foto);

    if ($stmt->execute()) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data mahasiswa berhasil ditambahkan.'];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal menambah data.'];
    }

    header("Location: mahasiswa.php");
    exit;
}



// =============== EDIT ===============
if ($aksi == "edit") {

    $prodi_id = $_POST['prodi_id'];
    $nim      = $_POST['nim'];
    $nama     = $_POST['nama'];
    $email    = $_POST['email'];
    $angkatan = $_POST['angkatan'];
    $id       = $_GET['id'];
    $foto_lama = $_POST['foto_lama'];

    if (empty($id) || empty($prodi_id) || empty($nim) || empty($nama) || empty($email) || empty($angkatan)) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Data tidak lengkap.'];
        header("Location: mahasiswa.php");
        exit;
    }

    // Upload foto baru jika ada
    $foto = $foto_lama;
    if (!empty($_FILES['foto']['name'])) {
        $foto_baru = time() . "_" . $_FILES['foto']['name'];
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $folder . $foto_baru)) {
            if (!empty($foto_lama) && file_exists($folder . $foto_lama)) {
                unlink($folder . $foto_lama);
            }
            $foto = $foto_baru;
        }
    }

    // Update data
    $stmt = $conn->prepare("
        UPDATE mahasiswa
        SET prodi_id=?, nim=?, nama=?, email=?, angkatan=?, foto=?
        WHERE id_mahasiswa=?
    ");

    $stmt->bind_param("isssisi", $prodi_id, $nim, $nama, $email, $angkatan, $foto, $id);

    if ($stmt->execute()) {
        $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data mahasiswa berhasil diperbarui.'];
    } else {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal memperbarui data.'];
    }

    header("Location: mahasiswa.php");
    exit;
}



// =============== HAPUS ===============
if ($aksi == "hapus") {

    if (empty($id)) {
        $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'ID tidak ditemukan.'];
        header("Location: mahasiswa.php");
        exit;
    }

    // Ambil foto lama
    $get = $conn->query("SELECT foto FROM mahasiswa WHERE id_mahasiswa = $id")->fetch_assoc();
    if (!empty($get['foto']) && file_exists($folder . $get['foto'])) {
        unlink($folder . $get['foto']);
    }

    $conn->query("DELETE FROM mahasiswa WHERE id_mahasiswa = $id");

    $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Data mahasiswa berhasil dihapus.'];
    header("Location: mahasiswa.php");
    exit;
}


// Jika aksi tidak ditemukan
header("Location: mahasiswa.php");
exit;
?>
