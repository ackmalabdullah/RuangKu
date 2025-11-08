<?php
$required_role = 'mahasiswa';

// 1. Memuat layout utama (header.php sudah mengurus koneksi & $koneksi)
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// 3. Logika PHP untuk mengambil data halaman ini
// Ambil ID mahasiswa dari session yang login
$id_mahasiswa_login = $_SESSION['id_mahasiswa'];

// Query untuk mengambil ID mahasiswa yang sedang login (hanya perlu ID untuk form)
$stmt_mhs = $koneksi->prepare("SELECT id_mahasiswa FROM mahasiswa WHERE id_mahasiswa = ?");
$stmt_mhs->bind_param("i", $id_mahasiswa_login);
$stmt_mhs->execute();
$result_mhs = $stmt_mhs->get_result();
$mhs = $result_mhs->fetch_assoc();
$stmt_mhs->close();
?>

<div class="container-xxl flex-grow-1 container-p-y">
  <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Profile /</span> Ganti Password</h4>

  <div class="row">
    <div class="col-md-12">

      <div class="card mb-4">
        <h5 class="card-header">Ganti Password</h5>
        <div class="card-body">
          <form id="formGantiPassword" method="POST" action="../../auth/proses_ganti_password.php">
            <input type="hidden" name="id_mahasiswa" value="<?= $mhs['id_mahasiswa']; ?>">

            <div class="row">
              <div class="mb-3 col-md-12 form-password-toggle">
                <label class="form-label" for="password_lama">Password Lama</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password_lama" class="form-control" name="password_lama" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <div class="mb-3 col-md-6 form-password-toggle">
                <label class="form-label" for="password_baru">Password Baru</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="password_baru" class="form-control" name="password_baru" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>

              <div class="mb-3 col-md-6 form-password-toggle">
                <label class="form-label" for="konfirmasi_password">Konfirmasi Password Baru</label>
                <div class="input-group input-group-merge">
                  <input type="password" id="konfirmasi_password" class="form-control" name="konfirmasi_password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required />
                  <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                </div>
              </div>
            </div>
            <button type="submit" name="simpan_password" class="btn btn-primary">Ubah Password</button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<?php
// 5. Logika SweetAlert (ini menerima pesan dari proses_ganti_password.php)
if (isset($_SESSION['pesan'])) {
  $pesan = $_SESSION['pesan'];
  $tipe_alert = ($pesan['tipe'] == 'success') ? 'success' : 'error';
  $judul_alert = ($pesan['tipe'] == 'success') ? 'Berhasil!' : 'Gagal!';

  echo "
<script>
    Swal.fire({
        title: '" . addslashes($judul_alert) . "',
        text: '" . addslashes($pesan['isi']) . "',
        icon: '" . $tipe_alert . "',
        confirmButtonText: 'OK'
    });
</script>
";
  unset($_SESSION['pesan']);
}


// 6. Memuat footer
require '../../../partials/mahasiswa/footer.php';
?>