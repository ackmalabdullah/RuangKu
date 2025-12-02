<?php
session_start();

// update path sesuai struktur proyekmu
require '../../../settings/koneksi.php';

$db = new Database();
$koneksi = $db->conn;

function redirect_with_alert($icon, $title, $text, $location = 'persetujuan_lab.php')
{
    $_SESSION['alert'] = [
        'icon' => $icon,
        'title' => $title,
        'text' => $text,
    ];
    header("Location: {$location}");
    exit();
}

// Pastikan user adalah pengelola_lab
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pengelola_lab') {
    redirect_with_alert('error', 'Akses Ditolak', 'Silakan login sebagai pengelola lab.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_alert('error', 'Akses Ditolak', 'Halaman ini hanya dapat diakses melalui pengajuan formulir.');
}

$id_peminjaman  = (int)($_POST['id_peminjaman'] ?? 0);
$action         = strtolower(trim($_POST['action'] ?? ''));
$id_user        = (int)($_SESSION['id_user'] ?? 0);
$tgl_persetujuan = date('Y-m-d H:i:s');

if ($id_peminjaman == 0 || !in_array($action, ['disetujui', 'ditolak'])) {
    redirect_with_alert('error', 'Aksi Invalid', 'Permintaan tidak lengkap atau tidak valid.');
}

// Ambil nama approver untuk log/pesan
$approved_by_name = 'Sistem/Admin';
$sql_get_name = "SELECT nama FROM users WHERE id_user = ?";
$stmt_name = mysqli_prepare($koneksi, $sql_get_name);
if ($stmt_name) {
    mysqli_stmt_bind_param($stmt_name, 'i', $id_user);
    mysqli_stmt_execute($stmt_name);
    $resn = mysqli_stmt_get_result($stmt_name);
    if ($rown = mysqli_fetch_assoc($resn)) {
        $approved_by_name = $rown['nama'];
    }
    mysqli_stmt_close($stmt_name);
}

// Ambil data peminjaman LAB yang masih 'menunggu'
$sql_check = "
    SELECT lab_id, status
    FROM peminjaman
    WHERE id_peminjaman = ? AND status = 'menunggu'
";
$stmt_check = mysqli_prepare($koneksi, $sql_check);
mysqli_stmt_bind_param($stmt_check, 'i', $id_peminjaman);
mysqli_stmt_execute($stmt_check);
$res_check = mysqli_stmt_get_result($stmt_check);
$peminjaman_data = mysqli_fetch_assoc($res_check);
mysqli_stmt_close($stmt_check);

if (!$peminjaman_data) {
    redirect_with_alert('warning', 'Peminjaman Tidak Ditemukan', 'Peminjaman tidak ditemukan atau sudah diproses.');
}

$lab_id = $peminjaman_data['lab_id'];
$tabel_jadwal = 'jadwal_lab';
$kolom_id_fk = 'lab_id';
$entity_id_value = $lab_id;

// Jika disetujui, cek konflik jadwal
if ($action === 'disetujui') {
    $sql_time = "
        SELECT tanggal_mulai, jam_mulai, jam_selesai
        FROM {$tabel_jadwal}
        WHERE peminjaman_id = ?
    ";
    $stmt_time = mysqli_prepare($koneksi, $sql_time);
    mysqli_stmt_bind_param($stmt_time, 'i', $id_peminjaman);
    mysqli_stmt_execute($stmt_time);
    $res_time = mysqli_stmt_get_result($stmt_time);
    $time_data = mysqli_fetch_assoc($res_time);
    mysqli_stmt_close($stmt_time);

    if (!$time_data) {
        redirect_with_alert('error', 'Data Waktu Hilang', 'Data waktu peminjaman tidak ditemukan di tabel jadwal.');
    }

    $tanggal_pinjam = $time_data['tanggal_mulai'];
    $jam_mulai = $time_data['jam_mulai'];
    $jam_selesai = $time_data['jam_selesai'];

    $sql_cek_konflik = "
        SELECT COUNT(id_jadwal) AS total_konflik
        FROM {$tabel_jadwal}
        WHERE {$kolom_id_fk} = ?
          AND tanggal_mulai = ?
          AND status_jadwal = 'Dipakai'
          AND (TIME(?) < jam_selesai AND TIME(?) > jam_mulai)
        LIMIT 1
    ";
    $stmt_konflik = mysqli_prepare($koneksi, $sql_cek_konflik);
    mysqli_stmt_bind_param($stmt_konflik, 'isss', $entity_id_value, $tanggal_pinjam, $jam_mulai, $jam_selesai);
    mysqli_stmt_execute($stmt_konflik);
    $res_konflik = mysqli_stmt_get_result($stmt_konflik);
    $konflik_count = (int) (mysqli_fetch_assoc($res_konflik)['total_konflik'] ?? 0);
    mysqli_stmt_close($stmt_konflik);

    if ($konflik_count > 0) {
        redirect_with_alert('error', 'Persetujuan Gagal', 'Waktu peminjaman bentrok dengan jadwal lain yang sudah Disetujui/Dipakai.');
    }
}

$new_peminjaman_status = $action; // 'disetujui' atau 'ditolak'
$new_jadwal_status = ($action === 'disetujui') ? 'Dipakai' : 'Ditolak';
$log_message = ($action === 'disetujui')
    ? 'Peminjaman LAB telah DISETUJUI oleh '.$approved_by_name.'. Jadwal lab sudah diaktifkan.'
    : 'Peminjaman LAB telah DITOLAK oleh '.$approved_by_name.'. Slot jadwal dikosongkan.';

mysqli_begin_transaction($koneksi);

try {
    // Update peminjaman
    $sql_update_peminjaman = "
        UPDATE peminjaman
        SET status = ?, approved_by = ?, updated_at = ?
        WHERE id_peminjaman = ? AND status = 'menunggu'
    ";
    $stmt_update_peminjaman = mysqli_prepare($koneksi, $sql_update_peminjaman);

    mysqli_stmt_bind_param(
        $stmt_update_peminjaman,
        'sisi',
        $new_peminjaman_status,
        $id_user,
        $tgl_persetujuan,
        $id_peminjaman
    );
    mysqli_stmt_execute($stmt_update_peminjaman);
    mysqli_stmt_close($stmt_update_peminjaman);

    // Update / hapus jadwal lab
    if ($action === 'ditolak') {
        $sql_update_jadwal = "DELETE FROM {$tabel_jadwal} WHERE peminjaman_id = ?";
        $stmt_update_jadwal = mysqli_prepare($koneksi, $sql_update_jadwal);
        mysqli_stmt_bind_param($stmt_update_jadwal, 'i', $id_peminjaman);
    } else {
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
    error_log("Gagal transaksi persetujuan LAB: " . $e->getMessage());

    redirect_with_alert('error', 'Aksi Gagal', 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.');
} finally {
    mysqli_close($koneksi);
}
?>
