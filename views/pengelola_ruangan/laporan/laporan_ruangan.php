<?php

$required_role = 'pengelola_ruangan';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$database = new Database();
$conn = $database->conn;

$query = mysqli_query($conn, "
    SELECT l.*, k.nama_kategori
    FROM ruangan l
    LEFT JOIN kategori_ruangan k ON k.id_kategori = l.kategori_id
    ORDER BY l.nama_ruangan ASC
");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">
    <h5 class="card-header">Laporan Data Ruangan</h5>
    <div class="card-body">

      <div class="mb-3 d-flex gap-2">
        <a href="cetak_pdf_ruangan.php" class="btn btn-info" target="_blank">
          <i class="bx bx-printer me-1"></i> Cetak PDF
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Lab</th>
              <th>Kategori</th>
              <th>Kapasitas</th>
              <th>Foto Ruangan</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($query) > 0){ 
              $no = 1;
              while($row = mysqli_fetch_assoc($query)){ ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><strong><?= $row['nama_ruangan']; ?></strong></td>
                <td><?= $row['nama_kategori']; ?></td>
                <td><?= $row['kapasitas']; ?></td>
                <td><?= $row['gambar']; ?></td>
              </tr>
            <?php }} else { ?>
              <tr>
                <td colspan="6" class="text-center">Tidak ada data.</td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

    </div>
  </div>

</div>

<?php require '../../../partials/mahasiswa/footer.php'; ?>
