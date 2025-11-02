<?php

$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
require '../../../settings/koneksi.php'; 

$database = new Database();
$koneksi = $database->conn;

$query = mysqli_query($koneksi, "SELECT id_user, nama, email, username, role 
                                 FROM users 
                                 WHERE role = 'pengelola_ruangan' OR role = 'pengelola_lab' 
                                 ORDER BY nama ASC");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <?php
  if (isset($_SESSION['pesan'])) {
      $pesan = $_SESSION['pesan'];
      echo '
      <div class="alert alert-' . $pesan['tipe'] . ' alert-dismissible" role="alert">
        ' . $pesan['isi'] . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      ';
      // Hapus session setelah ditampilkan
      unset($_SESSION['pesan']);
  }
  ?>
  
  <div class="card">
    <h5 class="card-header">Data Pengelola</h5>
    <div class="card-body">
      
      <div class="mb-3">
        <a href="form_pengelola.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Data Pengelola
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Username</th>
              <th>Role</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if (mysqli_num_rows($query) > 0) {
              $no = 1; 
              while ($data = mysqli_fetch_assoc($query)) {
            ?>
                <tr>
                  <td><?= $no++; ?></td>
                  <td>
                    <i class="bx bxs-user fa-lg text-primary me-3"></i> 
                    <strong><?= htmlspecialchars($data['nama']); ?></strong>
                  </td>
                  <td><?= htmlspecialchars($data['email']); ?></td>
                  <td><?= htmlspecialchars($data['username']); ?></td>
                  <td>
                    <?php
                    $badge_class = ($data['role'] == 'pengelola_ruangan') ? 'bg-label-info' : 'bg-label-success';
                    $role_name = ($data['role'] == 'pengelola_ruangan') ? 'Pengelola Ruangan' : 'Pengelola Lab';
                    ?>
                    <span class="badge <?= $badge_class; ?> me-1"><?= $role_name; ?></span>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="form_pengelola.php?id=<?= $data['id_user']; ?>">
                          <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a class="dropdown-item" href="proses_pengelola.php?aksi=hapus&id=<?= $data['id_user']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                          <i class="bx bx-trash me-1"></i> Delete
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
            <?php
              } // Akhir while
            } else {
            ?>
              <tr>
                <td colspan="6" class="text-center">Belum ada data pengelola.</td>
              </tr>
            <?php
            } // Akhir if-else
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  </div> <?php
// -----------------------------------------------------------------
// PERUBAHAN DI SINI: Blok PHP untuk SweetAlert
// -----------------------------------------------------------------
// Cek jika ada pesan dari session
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    // Tentukan tipe ikon berdasarkan 'tipe' pesan (success atau danger)
    $tipe_alert = ($pesan['tipe'] == 'success') ? 'success' : 'error';
    $judul_alert = ($pesan['tipe'] == 'success') ? 'Berhasil!' : 'Gagal!';
    
    // Cetak script JavaScript untuk SweetAlert
    echo "
    <script>
      Swal.fire({
        title: '" . $judul_alert . "',
        text: '" . $pesan['isi'] . "',
        icon: '" . $tipe_alert . "',
        confirmButtonText: 'OK'
      });
    </script>
    ";
    
    // Hapus session 'pesan' setelah ditampilkan
    unset($_SESSION['pesan']);
}
// -----------------------------------------------------------------
// BATAS AKHIR PERUBAHAN
// -----------------------------------------------------------------

require '../../../partials/mahasiswa/footer.php';
?>