<?php

$required_role = 'pengelola_lab';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$database = new Database();
$conn = $database->conn;

$query = mysqli_query($conn, "
    SELECT p.*, l.nama_lab, m.nama AS nama_mhs
    FROM peminjaman p
    LEFT JOIN laboratorium l ON l.id_lab = p.lab_id
    LEFT JOIN mahasiswa m ON m.id_mahasiswa = p.mahasiswa_id
    ORDER BY p.tanggal_pinjam DESC
");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">
    <h5 class="card-header">Laporan Peminjaman Laboratorium</h5>
    <div class="card-body">

      <div class="mb-3">
        <a href="cetak_pdf_peminjaman.php" class="btn btn-info" target="_blank">
          <i class="bx bx-printer me-1"></i> Cetak PDF
        </a>
      </div>

      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead>
            <tr>
              <th>No</th>
              <th>Mahasiswa</th>
              <th>Lab</th>
              <th>Tanggal</th>
              <th>Jam</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if(mysqli_num_rows($query) > 0){
              $no = 1;
              while($row = mysqli_fetch_assoc($query)){ ?>
              <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['nama_mhs']; ?></td>
                <td><?= $row['nama_lab']; ?></td>
                <td><?= $row['tanggal_pinjam']; ?></td>
                <td><?= $row['jam_mulai']." - ".$row['jam_selesai']; ?></td>
                <td><?= $row['status']; ?></td>
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
