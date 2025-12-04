<?php

session_start();

// Ganti path sesuai struktur proyek Anda
require '../../../settings/koneksi.php'; 

$db = new Database();
$koneksi = $db->conn;

if (!function_exists('redirect_with_alert')) {
    function redirect_with_alert($icon, $title, $text, $location = 'persetujuan.php')
    {
        $_SESSION['alert'] = [
            'icon' => $icon,
            'title' => $title,
            'text' => $text,
        ];
        header("Location: {$location}");
        exit();
    }
}

// Cek autentikasi dan role
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pengelola_ruangan') {
    redirect_with_alert('error', 'Akses Ditolak', 'Silakan login sebagai pengelola ruangan.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_alert('error', 'Akses Ditolak', 'Halaman ini hanya dapat diakses melalui pengajuan formulir.');
}

$id_peminjaman  = (int)($_POST['id_peminjaman'] ?? 0);
$action         = strtolower(trim($_POST['action'] ?? ''));
$id_user        = (int)($_SESSION['id_user'] ?? 0); // ID User yang menyetujui
$tgl_persetujuan = date('Y-m-d H:i:s');

if ($id_peminjaman == 0 || !in_array($action, ['disetujui', 'ditolak'])) {
    redirect_with_alert('error', 'Aksi Invalid', 'Permintaan tidak lengkap atau tidak valid.');
}

// =============================================
// 2.1. 👤 AMBIL NAMA LENGKAP USER (Hanya untuk Pesan Log)
// =============================================
$approved_by_name = 'Sistem/Admin'; 

$sql_get_name = "SELECT nama FROM users WHERE id_user = ?";
$stmt_name = mysqli_prepare($koneksi, $sql_get_name);
if ($stmt_name) {
    mysqli_stmt_bind_param($stmt_name, 'i', $id_user);
    mysqli_stmt_execute($stmt_name);
    $result_name = mysqli_stmt_get_result($stmt_name);
    if ($row_name = mysqli_fetch_assoc($result_name)) {
        $approved_by_name = $row_name['nama'];
    }
    mysqli_stmt_close($stmt_name);
}

$sql_get_data = "
    SELECT ruangan_id, lab_id, status, status_jurusan 
    FROM peminjaman 
    WHERE id_peminjaman = ? AND status = 'menunggu'
";
$stmt_get_data = mysqli_prepare($koneksi, $sql_get_data);
mysqli_stmt_bind_param($stmt_get_data, 'i', $id_peminjaman);
mysqli_stmt_execute($stmt_get_data);
$result_get_data = mysqli_stmt_get_result($stmt_get_data);
$peminjaman_data = mysqli_fetch_assoc($result_get_data);
mysqli_stmt_close($stmt_get_data);

if (!$peminjaman_data) {
    redirect_with_alert('warning', 'Peminjaman Tidak Ditemukan', 'Peminjaman tidak ditemukan atau sudah diproses.');
}

$ruangan_id = $peminjaman_data['ruangan_id'];
$lab_id = $peminjaman_data['lab_id'];
$status_jurusan = $peminjaman_data['status_jurusan'];
$is_ruangan = $ruangan_id !== null;

if ($is_ruangan && $status_jurusan !== 'disetujui') {
     $warning_text = 'Peminjaman ini belum disetujui oleh Jurusan. Pengelola ruangan tidak dapat memproses permintaan ini.';
     redirect_with_alert('warning', 'Akses Ditolak', $warning_text);
}

$tabel_jadwal = $is_ruangan ? 'jadwal_ruangan' : 'jadwal_lab';
$kolom_id_fk  = $is_ruangan ? 'ruangan_id' : 'lab_id';

if ($action === 'disetujui') {
}

$new_peminjaman_status = $action; // 'disetujui' atau 'ditolak'
$new_jadwal_status     = ($action === 'disetujui') ? 'Dipakai' : 'Ditolak';

$entity_name = $is_ruangan ? 'Ruangan' : 'Lab'; 
$log_message = ($action === 'disetujui') 
    ? "Peminjaman **{$entity_name}** telah **DISETUJUI FINAL** oleh {$approved_by_name}."
    : "Peminjaman **{$entity_name}** telah **DITOLAK FINAL** oleh {$approved_by_name}.";

mysqli_begin_transaction($koneksi);

try {
    $sql_update_peminjaman = "
        UPDATE peminjaman
        SET status = ?, approved_by = ?, updated_at = ?
        WHERE id_peminjaman = ? AND status = 'menunggu'
    ";
    $stmt_update_peminjaman = mysqli_prepare($koneksi, $sql_update_peminjaman);
    
    // Format binding 'sisi': status(s), approved_by(i), updated_at(s), id_peminjaman(i)
    mysqli_stmt_bind_param(
        $stmt_update_peminjaman, 
        'sisi', 
        $new_peminjaman_status, 
        $id_user,      
        $tgl_persetujuan,
        $id_peminjaman
    );
    
    mysqli_stmt_execute($stmt_update_peminjaman);
    // Cek apakah ada baris yang terpengaruh (pastikan proses hanya berjalan sekali)
    if (mysqli_stmt_affected_rows($stmt_update_peminjaman) == 0) {
        mysqli_rollback($koneksi);
        mysqli_stmt_close($stmt_update_peminjaman);
        redirect_with_alert('warning', 'Sudah Diproses', 'Peminjaman ini sudah diproses atau tidak valid.', 'persetujuan.php');
    }
    mysqli_stmt_close($stmt_update_peminjaman);
    
    
    // 5.2. UPDATE STATUS JADWAL / HAPUS JADWAL
    if ($action === 'ditolak') {
        // HAPUS slot jadwal dari tabel jadwal (karena ditolak)
        $sql_update_jadwal = "
            DELETE FROM {$tabel_jadwal}
            WHERE peminjaman_id = ?
        ";
        $stmt_update_jadwal = mysqli_prepare($koneksi, $sql_update_jadwal);
        mysqli_stmt_bind_param($stmt_update_jadwal, 'i', $id_peminjaman);

    } else { // Jika DISETUJUI, update status jadwal menjadi 'Dipakai'
        $sql_update_jadwal = "
            UPDATE {$tabel_jadwal}
            SET status_jadwal = ?, updated_at = ?
            WHERE peminjaman_id = ?
        ";
        $stmt_update_jadwal = mysqli_prepare($koneksi, $sql_update_jadwal);
        mysqli_stmt_bind_param($stmt_update_jadwal, 'ssi', $new_jadwal_status, $tgl_persetujuan, $id_peminjaman);
    }
    
    mysqli_stmt_execute($stmt_update_jadwal);
    mysqli_stmt_close($stmt_update_jadwal);

    mysqli_commit($koneksi);
    
    redirect_with_alert('success', 'Aksi Berhasil', $log_message);

} catch (Exception $e) {
    mysqli_rollback($koneksi);
    
    error_log("Gagal transaksi persetujuan: " . $e->getMessage()); 
    
    redirect_with_alert(
        'error',
        'Aksi Gagal',
        'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
    );
} finally {
    mysqli_close($koneksi);
}
?>