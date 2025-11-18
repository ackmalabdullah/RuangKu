<?php
// =============================================
// 1️⃣ INISIALISASI DASAR DAN CEK SESSION
// =============================================
session_start();

// Ganti path sesuai struktur proyek Anda
require '../../../settings/koneksi.php'; 

$db = new Database();
$koneksi = $db->conn;

// Fungsi redirect (Jika belum ada di utilities.php, tambahkan ini di proses_persetujuan.php)
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

// =============================================
// 2️⃣ VALIDASI REQUEST DAN SANITASI INPUT
// =============================================
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
$approved_by_name = 'Sistem/Admin'; // Default jika gagal ambil nama

// Mengambil nama dari tabel users (Koreksi nama tabel sudah diterapkan)
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

// =============================================
// 3️⃣ AMBIL DATA PEMINJAMAN SEBELUM PROSES & TENTUKAN ENTITAS
// =============================================
// Query untuk mendapatkan data peminjaman, termasuk ruangan_id/lab_id
$sql_check = "
    SELECT ruangan_id, lab_id, status 
    FROM peminjaman 
    WHERE id_peminjaman = ? AND status = 'menunggu'
";

$stmt_check = mysqli_prepare($koneksi, $sql_check);
mysqli_stmt_bind_param($stmt_check, 'i', $id_peminjaman);
mysqli_stmt_execute($stmt_check);
$result_check = mysqli_stmt_get_result($stmt_check);
$peminjaman_data = mysqli_fetch_assoc($result_check);
mysqli_stmt_close($stmt_check);

if (!$peminjaman_data) {
    redirect_with_alert('warning', 'Peminjaman Tidak Ditemukan', 'Peminjaman tidak ditemukan atau sudah diproses.');
}

$ruangan_id = $peminjaman_data['ruangan_id'];
$lab_id = $peminjaman_data['lab_id'];
$is_ruangan = $ruangan_id !== null;

// Tentukan tabel dan kolom yang akan digunakan
$tabel_jadwal = $is_ruangan ? 'jadwal_ruangan' : 'jadwal_lab';
$kolom_id_fk  = $is_ruangan ? 'ruangan_id' : 'lab_id';


// =============================================
// 3.1. ⚠️ CEK KONFLIK JADWAL AKTIF (Hanya Jika Aksi = 'disetujui')
// =============================================
if ($action === 'disetujui') {
    
    // 3.1.1. Ambil detail waktu peminjaman yang sedang diajukan (dari tabel jadwal)
    $sql_time = "
        SELECT tanggal_mulai, jam_mulai, jam_selesai 
        FROM {$tabel_jadwal} 
        WHERE peminjaman_id = ?
    ";
    $stmt_time = mysqli_prepare($koneksi, $sql_time);
    mysqli_stmt_bind_param($stmt_time, 'i', $id_peminjaman);
    mysqli_stmt_execute($stmt_time);
    $result_time = mysqli_stmt_get_result($stmt_time);
    $time_data = mysqli_fetch_assoc($result_time);
    mysqli_stmt_close($stmt_time);

    if (!$time_data) {
        redirect_with_alert('error', 'Data Waktu Hilang', 'Data waktu peminjaman tidak ditemukan di tabel jadwal. (Peminjaman ID: '.$id_peminjaman.')');
    }

    $tanggal_pinjam = $time_data['tanggal_mulai'];
    $jam_mulai = $time_data['jam_mulai'];
    $jam_selesai = $time_data['jam_selesai'];
    $entity_id_value = $is_ruangan ? $ruangan_id : $lab_id;

    // 3.1.2. Query Cek Konflik: Cari jadwal 'Dipakai' yang tumpang tindih
    $sql_cek_konflik = "
        SELECT COUNT(id_jadwal) AS total_konflik
        FROM {$tabel_jadwal}
        WHERE {$kolom_id_fk} = ? 
          AND tanggal_mulai = ? 
          AND status_jadwal = 'Dipakai' 
          AND (
                (TIME(?) < jam_selesai) 
                AND (TIME(?) > jam_mulai) 
          )
        LIMIT 1
    ";

    $stmt_konflik = mysqli_prepare($koneksi, $sql_cek_konflik);
    mysqli_stmt_bind_param($stmt_konflik, 'isss', $entity_id_value, $tanggal_pinjam, $jam_mulai, $jam_selesai);
    mysqli_stmt_execute($stmt_konflik);
    $result_konflik = mysqli_stmt_get_result($stmt_konflik);
    $konflik_count = mysqli_fetch_assoc($result_konflik)['total_konflik'];
    mysqli_stmt_close($stmt_konflik);

    if ($konflik_count > 0) {
        redirect_with_alert(
            'error',
            'Persetujuan Gagal',
            "Waktu peminjaman **bentrok** dengan jadwal lain yang sudah **Disetujui/Dipakai**.",
            'persetujuan.php'
        );
    }
}


// =============================================
// 4️⃣ PENENTUAN STATUS BARU
// =============================================
$new_peminjaman_status = $action; 
$new_jadwal_status = ($action === 'disetujui') ? 'Dipakai' : 'Ditolak'; 
$log_message = ($action === 'disetujui') 
    ? 'Peminjaman telah **DISYETUJUI** oleh **'.$approved_by_name.'**. Jadwal ruangan/lab sudah diaktifkan.'
    : 'Peminjaman telah **DITOLAK** oleh **'.$approved_by_name.'**. Slot jadwal dikosongkan.';


// =============================================
// 5️⃣ PROSES UPDATE MENGGUNAKAN TRANSAKSI
// =============================================
mysqli_begin_transaction($koneksi);

try {
    // 5.1. UPDATE STATUS PEMINJAMAN
    $sql_update_peminjaman = "
        UPDATE peminjaman
        SET status = ?, approved_by = ?, updated_at = ?
        WHERE id_peminjaman = ? AND status = 'menunggu'
    ";
    $stmt_update_peminjaman = mysqli_prepare($koneksi, $sql_update_peminjaman);
    
    // KOREKSI UTAMA DI SINI:
    // Approved_by adalah Foreign Key (INT) ke users.id_user,
    // sehingga binding harus menggunakan 'i' dan variabel $id_user.
    
    // Format binding 'sisi': status(s), approved_by(i), updated_at(s), id_peminjaman(i)
    mysqli_stmt_bind_param(
        $stmt_update_peminjaman, 
        'sisi', // <-- KOREKSI FORMAT BINDING
        $new_peminjaman_status, 
        $id_user,      // <-- KOREKSI: Menggunakan ID USER (INT)
        $tgl_persetujuan,
        $id_peminjaman
    );
    
    mysqli_stmt_execute($stmt_update_peminjaman);
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

    // 5.3. COMMIT TRANSAKSI
    mysqli_commit($koneksi);
    
    // 5.4. PESAN SUKSES
    redirect_with_alert('success', 'Aksi Berhasil', $log_message);

} catch (Exception $e) {
    // 5.5. ROLLBACK JIKA GAGAL
    mysqli_rollback($koneksi);
    
    // Log error secara internal untuk debugging
    error_log("Gagal transaksi persetujuan: " . $e->getMessage()); 
    
    // Tampilkan pesan error umum kepada pengguna
    redirect_with_alert(
        'error',
        'Aksi Gagal',
        'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.'
    );
} finally {
    mysqli_close($koneksi);
}
?>