<?php
session_start();
ob_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'peminjaman_error.log');

error_log("==========================================");
error_log("PROSES PEMINJAMAN RUANGAN - " . date('Y-m-d H:i:s'));
foreach ($_POST as $key => $value) {
    error_log("  POST[$key] = " . (is_array($value) ? json_encode($value) : $value));
}
error_log("Session mahasiswa_id: " . ($_SESSION['id_mahasiswa'] ?? 'tidak ada'));
error_log("==========================================");

function redirect_with_alert($icon, $title, $text, $location)
{
    $_SESSION['alert'] = ['icon' => $icon, 'title' => $title, 'text' => $text];
    if (ob_get_length()) ob_clean();
    header("Location: {$location}");
    exit();
}

if (!isset($_SESSION['id_mahasiswa']) || $_SESSION['role'] !== 'mahasiswa') {
    redirect_with_alert('error', 'Akses Ditolak', 'Silakan login sebagai mahasiswa.', '../../auth/login_mahasiswa.php');
}

require '../../../settings/koneksi.php';
$db = new Database();
$koneksi = $db->conn;

if ($koneksi->connect_error) {
    error_log("KONEKSI GAGAL: " . $koneksi->connect_error);
    die("Koneksi database gagal.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_alert('error', 'Invalid', 'Harus melalui form.');
}

// Ambil data
$peminjam_id     = (int)$_SESSION['id_mahasiswa'];
$ruangan_id      = (int)($_POST['entitas_id'] ?? 0);
$tanggal_pinjam  = $_POST['tanggal_pinjam'] ?? '';
$jam_mulai       = $_POST['jam_mulai'] ?? '';
$jam_selesai     = $_POST['jam_selesai'] ?? '';
$keperluan       = trim($_POST['keperluan'] ?? '');
$jumlah_peserta  = (int)($_POST['jumlah_peserta'] ?? 0);

$return_location = "form_peminjaman.php?tipe=ruangan&id_entitas={$ruangan_id}&tanggal={$tanggal_pinjam}";

// Validasi
if ($ruangan_id == 0 || empty($tanggal_pinjam) || empty($jam_mulai) || empty($jam_selesai) || empty($keperluan) || $jumlah_peserta < 1) {
    error_log("ERROR: Data tidak lengkap");
    redirect_with_alert('error', 'Data Tidak Lengkap', 'Semua kolom wajib diisi.', $return_location);
}

if ($jam_mulai >= $jam_selesai) {
    redirect_with_alert('warning', 'Waktu Tidak Valid', 'Jam selesai harus lebih besar dari jam mulai.', $return_location);
}

// Cek ruangan ada dan tersedia
error_log("Cek ruangan ID: $ruangan_id");
$sql = "SELECT nama_ruangan FROM ruangan WHERE id_ruangan = ? AND status_ruangan = 'tersedia'";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param('i', $ruangan_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    error_log("ERROR: Ruangan tidak ditemukan atau tidak tersedia");
    redirect_with_alert('warning', 'Tidak Tersedia', 'Ruangan tidak tersedia untuk dipinjam.', $return_location);
}
$ruangan = $result->fetch_assoc();
$nama_ruangan = $ruangan['nama_ruangan'];
$stmt->close();
error_log("Ruangan ditemukan: $nama_ruangan");

// Cek konflik jadwal
error_log("Cek konflik jadwal ruangan...");
$sql_konflik = "SELECT jam_mulai, jam_selesai, status_jadwal FROM jadwal_ruangan 
                WHERE ruangan_id = ? AND tanggal_mulai = ? 
                AND status_jadwal IN ('Dipakai','Perbaikan','Diblokir','Menunggu')
                AND ? < jam_selesai AND ? > jam_mulai";
$stmt = $koneksi->prepare($sql_konflik);
$stmt->bind_param('isss', $ruangan_id, $tanggal_pinjam, $jam_selesai, $jam_mulai);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $row = $res->fetch_assoc();
    $stmt->close();
    redirect_with_alert('warning', 'Jadwal Bentrok', "Waktu bentrok dengan jadwal <b>{$row['status_jadwal']}</b> ({$row['jam_mulai']} - {$row['jam_selesai']})", $return_location);
}
$stmt->close();
error_log("Tidak ada konflik");

// Transaksi
$koneksi->begin_transaction();

try {
    $sql_peminjaman = "INSERT INTO peminjaman 
    (mahasiswa_id, ruangan_id, lab_id, tanggal_pinjam, jam_mulai, jam_selesai, keperluan, jumlah_peserta, status, created_at)
    VALUES (?, ?, NULL, ?, ?, ?, ?, ?, 'menunggu', NOW())";
    $stmt = $koneksi->prepare($sql_peminjaman);
    $stmt->bind_param('iissssi', $peminjam_id, $ruangan_id, $tanggal_pinjam, $jam_mulai, $jam_selesai, $keperluan, $jumlah_peserta);
    $stmt->execute();
    $id_peminjaman = $koneksi->insert_id;
    $stmt->close();
    error_log("Berhasil insert peminjaman ID: $id_peminjaman");

    // Insert jadwal_ruangan
    $sql_jadwal = "INSERT INTO jadwal_ruangan 
        (ruangan_id, tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, status_jadwal, peminjaman_id, catatan, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, 'menunggu', ?, NULL, NOW(), NOW())";
    $stmt = $koneksi->prepare($sql_jadwal);
    $stmt->bind_param('issssi', $ruangan_id, $tanggal_pinjam, $tanggal_pinjam, $jam_mulai, $jam_selesai, $id_peminjaman);
    $stmt->execute();
    $stmt->close();
    error_log("Berhasil insert jadwal_ruangan");

    $koneksi->commit();
    error_log("TRANSAKSI RUANGAN BERHASIL");

    $_SESSION['alert'] = [
        'icon' => 'success',
        'title' => 'Pengajuan Berhasil!',
        'text' => "Peminjaman <b>Ruangan: $nama_ruangan</b> pada <b>$tanggal_pinjam</b> jam <b>$jam_mulai - $jam_selesai</b> berhasil diajukan."
    ];
    header("Location: ../riwayat/riwayat.php");
    exit();
} catch (Exception $e) {
    $koneksi->rollback();
    error_log("ERROR RUANGAN: " . $e->getMessage());
    redirect_with_alert('error', 'Gagal', 'Terjadi kesalahan: ' . $e->getMessage(), $return_location);
}
