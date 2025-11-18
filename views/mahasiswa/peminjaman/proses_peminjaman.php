<?php
// =============================================
// 1️⃣ INISIALISASI DASAR DAN CEK SESSION
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
session_start();

// Perbaikan: Lebih baik menggunakan satu fungsi redirect untuk semua kasus
function redirect_with_alert($icon, $title, $text, $location = 'peminjaman.php')
{
    $_SESSION['alert'] = [
        'icon' => $icon,
        'title' => $title,
        'text' => $text,
    ];
    header("Location: {$location}");
    exit();
}

if (!isset($_SESSION['id_mahasiswa']) || $_SESSION['role'] !== 'mahasiswa') {
    // Perbaikan: Arahkan ke login jika sesi tidak valid
    redirect_with_alert('error', 'Akses Ditolak', 'Silakan login sebagai mahasiswa untuk mengakses halaman ini.', '../../auth/login_mahasiswa.php');
}

// =============================================
// 2️⃣ KONEKSI DATABASE DAN UTILITAS
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
require '../../../settings/koneksi.php';
$db = new Database();
$koneksi = $db->conn;

// Cek koneksi
if ($koneksi->connect_error) {
    die("Koneksi database gagal: " . $koneksi->connect_error);
}

// =============================================
// 3️⃣ VALIDASI REQUEST DAN SANITASI INPUT
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_alert('error', 'Akses Ditolak', 'Halaman ini hanya dapat diakses melalui pengajuan formulir.');
}

// Perbaikan: Gunakan `mysqli_real_escape_string` untuk input teks (walaupun prepared statement sudah aman)
$peminjam_id     = (int)($_SESSION['id_mahasiswa'] ?? 0);
$tipe_entitas    = strtolower(trim($_POST['tipe_entitas'] ?? ''));
$entitas_id      = (int)($_POST['entitas_id'] ?? 0);
$tanggal_pinjam  = $_POST['tanggal_pinjam'] ?? '';
$jam_mulai       = $_POST['jam_mulai'] ?? '';
$jam_selesai     = $_POST['jam_selesai'] ?? '';
$keperluan       = mysqli_real_escape_string($koneksi, trim($_POST['keperluan'] ?? ''));
$jumlah_peserta  = (int)($_POST['jumlah_peserta'] ?? 0);

$return_location = "form_peminjaman.php?tipe={$tipe_entitas}&id_entitas={$entitas_id}&tanggal={$tanggal_pinjam}";


if (empty($tipe_entitas) || $entitas_id == 0 || empty($tanggal_pinjam) || empty($jam_mulai) || empty($jam_selesai) || empty($keperluan) || $jumlah_peserta < 1) {
    redirect_with_alert('error', 'Data Tidak Lengkap', 'Semua kolom wajib diisi.', $return_location);
}

if (!in_array($tipe_entitas, ['ruangan', 'laboratorium'])) {
    redirect_with_alert('error', 'Tipe Entitas Invalid', 'Tipe entitas yang diajukan tidak valid.');
}

if ($jam_mulai >= $jam_selesai) {
    redirect_with_alert('warning', 'Waktu Tidak Valid', 'Jam selesai harus lebih besar dari jam mulai.', $return_location);
}

// =============================================
// 4️⃣ VARIABEL DINAMIS BERDASARKAN TIPE
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
$tabel_entitas       = ($tipe_entitas === 'ruangan') ? 'ruangan' : 'laboratorium';
$kolom_id_entitas    = ($tipe_entitas === 'ruangan') ? 'id_ruangan' : 'id_lab';
$kolom_nama_entitas  = ($tipe_entitas === 'ruangan') ? 'nama_ruangan' : 'nama_lab';
$tabel_jadwal        = ($tipe_entitas === 'ruangan') ? 'jadwal_ruangan' : 'jadwal_lab';
$kolom_id_jadwal_fk  = ($tipe_entitas === 'ruangan') ? 'ruangan_id' : 'lab_id';
$nama_entitas_kapital = ucfirst($tipe_entitas);

// $ruangan_id_value atau $lab_id_value akan bernilai NULL
$ruangan_id_value = ($tipe_entitas === 'ruangan') ? $entitas_id : null;
$lab_id_value     = ($tipe_entitas === 'laboratorium') ? $entitas_id : null;

// Status peminjaman selalu 'menunggu' saat pengajuan awal
$status_peminjaman = 'menunggu';
$tgl_pengajuan = date('Y-m-d H:i:s');

// =============================================
// 5️⃣ CEK KONFLIK JADWAL
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
// Status jadwal yang dianggap konflik adalah yang aktif
$status_konflik_array = ['Dipakai', 'Perbaikan', 'Diblokir'];
$status_konflik_in = "'" . implode("','", $status_konflik_array) . "'"; // Membentuk string IN ('Dipakai','Perbaikan','Diblokir')

$sql_cek_jadwal = "
    SELECT jam_mulai, jam_selesai, status_jadwal
    FROM {$tabel_jadwal}
    WHERE {$kolom_id_jadwal_fk} = ? 
      AND tanggal_mulai = ? 
      AND status_jadwal IN ({$status_konflik_in})
      AND (
          (TIME(?) < jam_selesai) 
          AND (TIME(?) > jam_mulai)
      )
";

$stmt = mysqli_prepare($koneksi, $sql_cek_jadwal);
// Perbaikan: Gunakan 'isss' untuk memastikan $entitas_id di-bind sebagai integer (i)
mysqli_stmt_bind_param($stmt, 'isss', $entitas_id, $tanggal_pinjam, $jam_mulai, $jam_selesai);
mysqli_stmt_execute($stmt);
$result_cek = mysqli_stmt_get_result($stmt);
mysqli_stmt_close($stmt);

if (mysqli_num_rows($result_cek) > 0) {
    $row = mysqli_fetch_assoc($result_cek);
    $status_konflik = $row['status_jadwal'];
    redirect_with_alert(
        'warning',
        'Peminjaman Gagal',
        "Waktu bentrok dengan jadwal lain berstatus <b>{$status_konflik}</b> ({$row['jam_mulai']} - {$row['jam_selesai']}).",
        $return_location
    );
}

// =============================================
// 6️⃣ AMBIL NAMA ENTITAS
// [TIDAK ADA PERUBAHAN DI SINI]
// =============================================
// Query untuk mendapatkan nama entitas (agar pesan sukses lebih informatif)
$stmt_nama = mysqli_prepare($koneksi, "SELECT {$kolom_nama_entitas} FROM {$tabel_entitas} WHERE {$kolom_id_entitas} = ?");
mysqli_stmt_bind_param($stmt_nama, 'i', $entitas_id);
mysqli_stmt_execute($stmt_nama);
$result_nama = mysqli_stmt_get_result($stmt_nama);
$nama_entitas = mysqli_fetch_assoc($result_nama)[$kolom_nama_entitas] ?? $nama_entitas_kapital;
mysqli_stmt_close($stmt_nama);


// =============================================
// 7️⃣ SIMPAN DATA DALAM TRANSAKSI
// =============================================
mysqli_begin_transaction($koneksi);

try {
    // --- INSERT PEMINJAMAN ---
    $sql_insert = "
        INSERT INTO peminjaman (
            mahasiswa_id, ruangan_id, lab_id,
            tanggal_pinjam, jam_mulai, jam_selesai,
            keperluan, jumlah_peserta, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt_insert = mysqli_prepare($koneksi, $sql_insert);

    // 👇👇 PERBAIKAN UTAMA: MENGUBAH 'iiisssssis' MENJADI 'issssssis' 👇👇
    // Alasan: ruangan_id dan lab_id diizinkan NULL, dan harus di-bind sebagai 's' (string) 
    // agar tidak error ketika nilainya NULL di MySQLi Strict Mode.
    // Urutan tipe: i (mahasiswa_id), s (ruangan_id), s (lab_id), s (tgl_pinjam), s (jam_mulai), s (jam_selesai), s (keperluan), i (jml_peserta), s (status), s (created_at)
    mysqli_stmt_bind_param(
        $stmt_insert,
        'issssssiss', // <-- GANTI DENGAN INI. Ini adalah 10 karakter
        $peminjam_id,
        $ruangan_id_value,
        $lab_id_value,
        $tanggal_pinjam,
        $jam_mulai,
        $jam_selesai,
        $keperluan,
        $jumlah_peserta,
        $status_peminjaman,
        $tgl_pengajuan
    );
    // 👆👆 AKHIR PERBAIKAN TYPE BINDING 👆👆

    mysqli_stmt_execute($stmt_insert);
    $id_peminjaman_baru = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt_insert);


    // --- INSERT JADWAL ---
    $sql_jadwal = "
        INSERT INTO {$tabel_jadwal} (
            {$kolom_id_jadwal_fk}, tanggal_mulai, tanggal_selesai,
            jam_mulai, jam_selesai, status_jadwal, peminjaman_id
        ) VALUES (?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt_jadwal = mysqli_prepare($koneksi, $sql_jadwal);

    // ASUMSI: Anda sudah menjalankan SQL untuk menambahkan 'Menunggu' ke ENUM di tabel jadwal_ruangan/lab
    $status_jadwal_pending = 'Menunggu';

    mysqli_stmt_bind_param(
        $stmt_jadwal,
        'isssssi',
        $entitas_id,
        $tanggal_pinjam,
        $tanggal_pinjam,
        $jam_mulai,
        $jam_selesai,
        $status_jadwal_pending,
        $id_peminjaman_baru
    );
    mysqli_stmt_execute($stmt_jadwal);
    mysqli_stmt_close($stmt_jadwal);

    // --- COMMIT TRANSAKSI ---
    mysqli_commit($koneksi);

    // --- PESAN SUKSES ---
    $pesan_sukses = "Peminjaman <b>{$nama_entitas_kapital}: {$nama_entitas}</b> pada tanggal <b>{$tanggal_pinjam}</b> telah diajukan.<br>Status: <b>Menunggu Persetujuan</b>.";
    redirect_with_alert('success', 'Pengajuan Berhasil', $pesan_sukses, '../riwayat/riwayat.php');
} catch (Exception $e) {
    // --- ROLLBACK TRANSAKSI ---
    mysqli_rollback($koneksi);
    // Tambahkan detail error ke log (hanya untuk server side)
    error_log("Gagal transaksi pengajuan: " . $e->getMessage());

    // Perbaikan pesan alert: tampilkan pesan error yang lebih informatif (opsional, bagus untuk debugging)
    $error_detail = 'Terjadi kesalahan saat menyimpan data. Pastikan semua entitas memiliki nilai ENUM yang benar.';

    redirect_with_alert(
        'error',
        'Pengajuan Gagal',
        $error_detail,
        $return_location
    );
} finally {
    mysqli_close($koneksi);
}
