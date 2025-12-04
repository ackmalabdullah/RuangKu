<?php
session_start();
require '../../../settings/koneksi.php';

$database = new Database();
$koneksi = $database->conn;

if (isset($_GET['aksi'])) {
    $aksi = $_GET['aksi'];

    if ($aksi == 'tambah' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
        if ($nama_fasilitas === '') {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'Nama fasilitas wajib diisi.'];
            header('Location: form_fasilitas_ruangan.php');
            exit;
        }

        $stmt = $koneksi->prepare("INSERT INTO fasilitas (nama_fasilitas) VALUES (?)");
        $stmt->bind_param("s", $nama_fasilitas);
        if ($stmt->execute()) {
            $_SESSION['pesan'] = ['tipe'=>'success','isi'=>'Fasilitas berhasil ditambahkan.'];
        } else {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'Gagal menambahkan: '.$stmt->error];
        }
        $stmt->close();
        header('Location: fasilitas_ruangan.php');
        exit;
    }

    elseif ($aksi == 'edit' && $_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_fasilitas = (int) ($_POST['id_fasilitas'] ?? 0);
        $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');

        if ($id_fasilitas <= 0 || $nama_fasilitas === '') {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'Data tidak valid.'];
            header('Location: fasilitas_ruangan.php');
            exit;
        }

        $stmt = $koneksi->prepare("UPDATE fasilitas SET nama_fasilitas = ? WHERE id_fasilitas = ?");
        $stmt->bind_param("si", $nama_fasilitas, $id_fasilitas);
        if ($stmt->execute()) {
            $_SESSION['pesan'] = ['tipe'=>'success','isi'=>'Fasilitas berhasil diperbarui.'];
        } else {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'Gagal update: '.$stmt->error];
        }
        $stmt->close();
        header('Location: fasilitas_ruangan.php');
        exit;
    }

    elseif ($aksi == 'hapus') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'ID tidak valid.'];
            header('Location: fasilitas_ruangan.php');
            exit;
        }

        // cek relasi di fasilitas_ruangan (jika ada, tolak hapus)
        $stmt_check = $koneksi->prepare("SELECT COUNT(*) AS cnt FROM fasilitas_ruangan WHERE fasilitas_id = ?");
        $stmt_check->bind_param("i", $id);
        $stmt_check->execute();
        $row = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();

        if ($row['cnt'] > 0) {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>"Tidak dapat menghapus! Fasilitas ini masih digunakan pada {$row['cnt']} ruangan."];
            header('Location: fasilitas_ruangan.php');
            exit;
        }

        $stmt = $koneksi->prepare("DELETE FROM fasilitas WHERE id_fasilitas = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['pesan'] = ['tipe'=>'success','isi'=>'Fasilitas berhasil dihapus.'];
        } else {
            $_SESSION['pesan'] = ['tipe'=>'danger','isi'=>'Gagal menghapus: '.$stmt->error];
        }
        $stmt->close();
        header('Location: fasilitas_ruangan.php');
        exit;
    }
}

// Jika aksi tidak valid
header('Location: fasilitas_ruangan.php');
exit;
