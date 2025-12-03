<?php
// FILE: views/jurusan/persetujuan/proses_persetujuan.php

// =============================================
// 1️⃣ INISIALISASI DASAR DAN CEK SESSION
// =============================================
session_start();

// Ganti path sesuai struktur proyek Anda
require '../../../settings/koneksi.php'; 

$db = new Database();
$koneksi = $db->conn;

// Fungsi redirect (jika belum ada)
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
// >>> KOREKSI 1: Cek session menggunakan id_user dan role 'jurusan' dari tabel users <<<
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'jurusan') {
    redirect_with_alert('error', 'Akses Ditolak', 'Silakan login sebagai Jurusan.', '../../../auth/login.php');
}

// =============================================
// 2️⃣ VALIDASI REQUEST DAN SANITASI INPUT
// =============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_alert('error', 'Akses Ditolak', 'Halaman ini hanya dapat diakses melalui pengajuan formulir.');
}

$id_peminjaman = (int)($_POST['id_peminjaman'] ?? 0);
// Ambil dari input form/tombol
$action_jurusan = strtolower(trim($_POST['action'] ?? '')); 
// Kolom ini ada di DB (image_2861be.png)
$catatan_jurusan = trim($_POST['catatan_jurusan'] ?? NULL); 

// >>> KOREKSI 2: Ambil ID Jurusan dari $_SESSION['id_user'] karena Jurusan terdaftar di tabel users <<<
$id_jurusan = (int)($_SESSION['id_user'] ?? 0); 

$tgl_persetujuan = date('Y-m-d H:i:s');
$redirect_to = 'persetujuan.php';

if ($id_peminjaman == 0 || !in_array($action_jurusan, ['disetujui', 'ditolak'])) {
    redirect_with_alert('error', 'Aksi Invalid', 'Permintaan tidak lengkap atau tidak valid.', $redirect_to);
}

// =============================================
// 3️⃣ PROSES UPDATE MENGGUNAKAN TRANSAKSI
// =============================================
mysqli_begin_transaction($koneksi);

try {
    // Tentukan status akhir peminjaman utama dan status jadwal
    // Jika DISETUJUI oleh Jurusan: Status utama tetap 'menunggu' untuk diverifikasi Pengelola Ruangan.
    // Jika DITOLAK oleh Jurusan: Status utama berubah menjadi 'ditolak'.
    $new_status_peminjaman = ($action_jurusan === 'disetujui') ? 'menunggu' : 'ditolak'; 
    $new_jadwal_status = ($action_jurusan === 'ditolak') ? 'Ditolak' : 'Menunggu'; // Ditolak atau Menunggu Pengelola
    
    $log_message = ($action_jurusan === 'disetujui') 
        ? 'Peminjaman telah **DISETUJUI** oleh Jurusan. Selanjutnya diteruskan ke Pengelola Ruangan.'
        : 'Peminjaman telah **DITOLAK** oleh Jurusan. Proses dibatalkan.';


    // 3.1. UPDATE STATUS PEMINJAMAN (status_jurusan dan status utama)
    // Kolom jurusan_approved_by (int) sesuai dengan id_user Jurusan (image_2861be.png)
    $sql_update_peminjaman = "
        UPDATE peminjaman
        SET 
            status = ?, 
            status_jurusan = ?, 
            catatan_jurusan = ?, 
            tgl_jurusan_approve = ?, 
            jurusan_approved_by = ?,
            updated_at = ?
        WHERE id_peminjaman = ? AND status_jurusan = 'menunggu'
    ";
    
    $stmt_update_peminjaman = mysqli_prepare($koneksi, $sql_update_peminjaman);
    
    // Format binding 'ssssisi': status(s), status_jurusan(s), catatan(s), tgl(s), approved_by(i), updated_at(s), id_peminjaman(i)
    mysqli_stmt_bind_param(
        $stmt_update_peminjaman, 
        'ssssisi', 
        $new_status_peminjaman, // Status utama
        $action_jurusan,        // Status Jurusan: disetujui/ditolak
        $catatan_jurusan, 
        $tgl_persetujuan, 
        $id_jurusan,            // Berasal dari $_SESSION['id_user']
        $tgl_persetujuan,
        $id_peminjaman
    );
    
    mysqli_stmt_execute($stmt_update_peminjaman);
    
    // Cek apakah ada baris yang terpengaruh (pastikan proses hanya berjalan sekali)
    if (mysqli_stmt_affected_rows($stmt_update_peminjaman) == 0) {
        mysqli_rollback($koneksi);
        mysqli_stmt_close($stmt_update_peminjaman);
        redirect_with_alert('warning', 'Sudah Diproses', 'Peminjaman ini sudah diproses atau tidak valid.', $redirect_to);
    }
    mysqli_stmt_close($stmt_update_peminjaman);
    
    
    // 3.2. UPDATE STATUS JADWAL
    $sql_update_jadwal = "
        UPDATE jadwal_ruangan
        SET status_jadwal = ?, updated_at = ?
        WHERE peminjaman_id = ?
    ";
    $stmt_update_jadwal = mysqli_prepare($koneksi, $sql_update_jadwal);
    mysqli_stmt_bind_param($stmt_update_jadwal, 'ssi', $new_jadwal_status, $tgl_persetujuan, $id_peminjaman);
    
    mysqli_stmt_execute($stmt_update_jadwal);
    mysqli_stmt_close($stmt_update_jadwal);

    // 3.3. COMMIT TRANSAKSI
    mysqli_commit($koneksi);
    
    // 3.4. PESAN SUKSES
    redirect_with_alert('success', 'Aksi Berhasil', $log_message, $redirect_to);

} catch (Exception $e) {
    // 3.5. ROLLBACK JIKA GAGAL
    mysqli_rollback($koneksi);
    
    error_log("Gagal transaksi persetujuan Jurusan: " . $e->getMessage()); 
    
    redirect_with_alert(
        'error',
        'Aksi Gagal',
        'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.',
        $redirect_to
    );
} finally {
    mysqli_close($koneksi);
}
?>