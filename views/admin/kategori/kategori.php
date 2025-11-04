<?php

$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
require '../../../settings/koneksi.php'; 

$database = new Database();
$koneksi = $database->conn;

// Query untuk menampilkan data kategori ruangan
$query = mysqli_query($koneksi, "SELECT id_kategori, nama_kategori 
                                 FROM kategori_ruangan 
                                 ORDER BY nama_kategori ASC");
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
      unset($_SESSION['pesan']);
  }
  ?>
  
  <div class="card">
    <h5 class="card-header">Data Kategori Ruangan</h5>
    <div class="card-body">
      
      <div class="mb-3">
        <a href="form_kategori.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Kategori Ruangan
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Kategori</th>
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
                    <i class="bx bxs-building fa-lg text-primary me-3"></i> 
                    <strong><?= htmlspecialchars($data['nama_kategori']); ?></strong>
                  </td>
                  <td>
                    <div class="dropdown">
                      <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                      </button>
                      <div class="dropdown-menu">
                        <a class="dropdown-item" href="form_kategori.php?id=<?= $data['id_kategori']; ?>">
                          <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a class="dropdown-item" href="proses_kategori.php?aksi=hapus&id=<?= $data['id_kategori']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">
                          <i class="bx bx-trash me-1"></i> Delete
                        </a>
                      </div>
                    </div>
                  </td>
                </tr>
            <?php
              }
            } else {
            ?>
              <tr>
                <td colspan="3" class="text-center">Belum ada data kategori ruangan.</td>
              </tr>
            <?php
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php
// -----------------------------------------------------------------
// SWEETALERT UNTUK NOTIFIKASI
// -----------------------------------------------------------------
if (isset($_SESSION['pesan'])) {
    $pesan = $_SESSION['pesan'];
    $tipe_alert = ($pesan['tipe'] == 'success') ? 'success' : 'error';
    $judul_alert = ($pesan['tipe'] == 'success') ? 'Berhasil!' : 'Gagal!';
    
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
    
    unset($_SESSION['pesan']);
}

require '../../../partials/mahasiswa/footer.php';
?>
