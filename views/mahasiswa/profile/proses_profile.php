<?php
// 1. Mulai Session
session_start();

// 2. Memuat Koneksi Database
require '../../../settings/koneksi.php';

$database = new Database();
$koneksi = $database->conn;

// 3. Cek apakah tombol 'simpan_profil' ditekan
if (isset($_POST['simpan_profil'])) {

  // 4. Ambil data dari formulir
  $id_mahasiswa = $_POST['id_mahasiswa'];
  $nama = $_POST['nama'];
  $email = $_POST['email'];
  $angkatan = $_POST['angkatan'];
  $prodi_id = $_POST['prodi_id'];

  $nama_file_foto = null; // Variabel untuk menyimpan nama file foto baru (jika ada)
  $upload_ok = true;
  $pesan_error = "";

  // 5. Logika untuk Handle Upload Foto (jika ada file baru di-upload)
  // Cek apakah ada file yang diupload dan tidak ada error
  if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0 && $_FILES['foto_profil']['size'] > 0) {

    // Tentukan folder target berdasarkan lokasi file ini
    // 'proses_profile.php' ada di 'views/mahasiswa/profile/'
    // 'assets/' ada di root, jadi kita perlu naik 3 level
    $target_dir = "../../../assets/img/avatars/";

    $file_name = basename($_FILES['foto_profil']['name']);
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Buat nama file unik: profil_[id_mahasiswa]_[timestamp].ext
    $new_file_name = "profil_" . $id_mahasiswa . "_" . time() . "." . $file_ext;
    $target_file = $target_dir . $new_file_name;

    // --- Validasi Sisi Server ---

    // 5a. Validasi Ukuran File (800KB = 800 * 1024 bytes)
    if ($_FILES['foto_profil']['size'] > 819200) { // 800KB
      $pesan_error = "Ukuran file terlalu besar. Ukuran maksimal adalah 800KB.";
      $upload_ok = false;
    }

    // 5b. Validasi Tipe File
    $allowed_ext = ['jpg', 'jpeg', 'png'];
    if (!in_array($file_ext, $allowed_ext)) {
      $pesan_error = "Tipe file tidak diizinkan. Hanya file JPG, JPEG, atau PNG yang diperbolehkan.";
      $upload_ok = false;
    }

    // 5c. Jika semua validasi lolos, proses upload
    if ($upload_ok) {

      // Ambil nama foto lama untuk dihapus
      $stmt_old_foto = $koneksi->prepare("SELECT foto FROM mahasiswa WHERE id_mahasiswa = ?");
      $stmt_old_foto->bind_param("i", $id_mahasiswa);
      $stmt_old_foto->execute();
      $result_old_foto = $stmt_old_foto->get_result();

      if ($result_old_foto->num_rows > 0) {
        $old_foto = $result_old_foto->fetch_assoc()['foto'];
      }
      $stmt_old_foto->close();

      // Pindahkan file baru ke folder target
      if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $target_file)) {
        $nama_file_foto = $new_file_name; // Set nama file baru untuk disimpan ke DB

        // Hapus foto lama jika ada, dan bukan foto default (misal: 'default.png')
        if (isset($old_foto) && $old_foto && $old_foto != 'default.png' && file_exists($target_dir . $old_foto)) {
          unlink($target_dir . $old_foto);
        }
      } else {
        $pesan_error = "Terjadi kesalahan saat mengupload file baru.";
        $upload_ok = false;
      }
    }
  } // Selesai handle upload

  // 6. Persiapkan dan Eksekusi Query SQL

  // Jika terjadi error saat validasi upload, jangan update database
  if (!$upload_ok) {
    $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => $pesan_error];
  } else {

    // Jika ada file foto baru yang diupload ($nama_file_foto tidak null)
    if ($nama_file_foto !== null) {
      $sql = "UPDATE mahasiswa 
                    SET nama = ?, email = ?, angkatan = ?, prodi_id = ?, foto = ? 
                    WHERE id_mahasiswa = ?";
      $stmt = $koneksi->prepare($sql);
      // Tipe data: s=string, i=integer
      // nama(s), email(s), angkatan(i), prodi_id(i), foto(s), id_mahasiswa(i)
      $stmt->bind_param("ssiisi", $nama, $email, $angkatan, $prodi_id, $nama_file_foto, $id_mahasiswa);
    } else {
      // Jika tidak ada file foto baru (hanya update data teks)
      $sql = "UPDATE mahasiswa 
                    SET nama = ?, email = ?, angkatan = ?, prodi_id = ? 
                    WHERE id_mahasiswa = ?";
      $stmt = $koneksi->prepare($sql);
      // Tipe data: s=string, i=integer
      // nama(s), email(s), angkatan(i), prodi_id(i), id_mahasiswa(i)
      $stmt->bind_param("ssiii", $nama, $email, $angkatan, $prodi_id, $id_mahasiswa);
    }

    // 7. Eksekusi query dan beri feedback
    if ($stmt->execute()) {
      $_SESSION['pesan'] = ['tipe' => 'success', 'isi' => 'Profil berhasil diperbarui.'];

      // *** [PENYESUAIAN PENTING] ***
      // Jika nama berubah, kita update session agar navbar langsung menampilkan nama baru
      $_SESSION['nama'] = $nama;
    } else {
      $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Gagal memperbarui profil: ' . $stmt->error];
    }
    $stmt->close();
  }
} else {
  // Jika file diakses langsung tanpa menekan tombol simpan
  $_SESSION['pesan'] = ['tipe' => 'error', 'isi' => 'Akses tidak sah.'];
}

// 8. Tutup koneksi dan Redirect kembali ke halaman profile
$koneksi->close();
header("Location: profile.php");
exit;
