<?php

$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Koneksi dari version Raka (lebih aman dan fleksibel)
$koneksi_path = dirname(_DIR_, 3) . '/settings/koneksi.php';

if (!file_exists($koneksi_path)) {
    die('ERROR: File koneksi.php tidak ditemukan di path ' . $koneksi_path);
}
require $koneksi_path;

$database = new Database();
$conn = $database->conn;

// Query untuk menampilkan data fasilitas
$query = mysqli_query($conn, "SELECT id_fasilitas, nama_fasilitas, created_at
                                 FROM fasilitas 
                                 ORDER BY nama_fasilitas ASC");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">
    <h5 class="card-header">Data Fasilitas</h5>
    <div class="card-body">

      <div class="mb-3 d-flex gap-2">
        <a href="form_fasilitas.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Fasilitas
        </a>
        <a href="cetak_pdf.php" class="btn btn-info" target="_blank">
          <i class="bx bx-printer me-1"></i> Cetak PDF
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Fasilitas</th>
              <th width="200">Aksi</th>
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
                    <i class="bx bx-list-check fa-lg text-primary me-2"></i>
                    <strong><?= htmlspecialchars($data['nama_fasilitas']); ?></strong>
                  </td>
                  <td>
                    <div>
                      <a class="btn btn-warning btn-sm me-1" href="form_fasilitas.php?id=<?= $data['id_fasilitas']; ?>">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                      </a>

                      <a class="btn btn-danger btn-sm btn-delete" 
                         href="proses_fasilitas.php?aksi=hapus&id=<?= $data['id_fasilitas']; ?>">
                        <i class="bx bx-trash me-1"></i> Delete
                      </a>
                    </div>
                  </td>
                </tr>
              <?php
              }
            } else {
              ?>
              <tr>
                <td colspan="3" class="text-center">Belum ada data fasilitas.</td>
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

<script>
  // Konfirmasi hapus dengan SweetAlert
  document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', function(event) {
      event.preventDefault();
      const deleteUrl = this.href;

      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data yang dihapus tidak dapat dikembalikan!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = deleteUrl;
        }
      });
    });
  });
</script>

<?php
// SWEETALERT SESSION
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

require '../../../partials/mahasiswa/footer.php';
?>