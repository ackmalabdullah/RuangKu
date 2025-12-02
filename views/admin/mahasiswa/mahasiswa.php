<?php

$required_role = 'admin'; 

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

$database = new Database();
$conn = $database->conn;

// Query mahasiswa + join prodi
$query = mysqli_query($conn, "
    SELECT m.id_mahasiswa, m.nim, m.nama, m.email, m.angkatan, m.foto,
           p.nama_prodi
    FROM mahasiswa m
    LEFT JOIN prodi p ON p.id_prodi = m.prodi_id
    ORDER BY m.nama ASC
");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <div class="card">
    <h5 class="card-header">Data Mahasiswa</h5>
    <div class="card-body">

      <div class="mb-3 d-flex gap-2">
        <a href="form_mahasiswa.php" class="btn btn-primary">
          <i class="bx bx-plus me-1"></i> Tambah Mahasiswa
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
              <th>NIM</th>
              <th>Nama</th>
              <th>Email</th>
              <th>Prodi</th>
              <th>Angkatan</th>
              <th>Foto</th>
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
                  <td><?= htmlspecialchars($data['nim']); ?></td>
                  <td><strong><?= htmlspecialchars($data['nama']); ?></strong></td>
                  <td><?= htmlspecialchars($data['email']); ?></td>
                  <td><?= htmlspecialchars($data['nama_prodi']); ?></td>
                  <td><?= htmlspecialchars($data['angkatan']); ?></td>

                  <!-- FOTO -->
                  <td>
                    <?php if (!empty($data['foto'])): ?>
                        <img src="/RuangKu/assets/img/avatars/<?= $data['foto']; ?>" 
                            style="width: 75px; height: 100px; object-fit: cover; border-radius: 6px;"
                            alt="foto">
                    <?php else: ?>
                        <img src="/RuangKu/assets/img/avatars/default.png" 
                            style="width: 75px; height: 100px; object-fit: cover; border-radius: 6px;"
                            alt="default">
                    <?php endif; ?>
                </td>

                  <!-- Aksi -->
                  <td>
                    <a href="form_mahasiswa.php?id=<?= $data['id_mahasiswa']; ?>" 
                       class="btn btn-warning btn-sm me-1">
                      <i class="bx bx-edit-alt"></i> Edit
                    </a>

                    <a href="proses_mahasiswa.php?aksi=hapus&id=<?= $data['id_mahasiswa']; ?>" 
                       class="btn btn-danger btn-sm btn-delete">
                      <i class="bx bx-trash"></i> Delete
                    </a>
                  </td>
                </tr>
            <?php
              }
            } else {
              echo '<tr><td colspan="8" class="text-center">Belum ada data mahasiswa.</td></tr>';
            }
            ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// SweetAlert Delete
document.querySelectorAll('.btn-delete').forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const deleteUrl = this.href;

    Swal.fire({
      title: 'Hapus data?',
      text: 'Data yang dihapus tidak dapat dikembalikan!',
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
// SweetAlert session
if (isset($_SESSION['pesan'])) {
  $p = $_SESSION['pesan'];

  echo "
  <script>
    Swal.fire({
      title: '" . ($p['tipe']=='success'?'Berhasil!':'Gagal!') . "',
      text: '". addslashes($p['isi']) ."',
      icon: '". $p['tipe'] ."'
    });
  </script>";

  unset($_SESSION['pesan']);
}

require '../../../partials/mahasiswa/footer.php';
?>
