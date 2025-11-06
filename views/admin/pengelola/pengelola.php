<?php

$required_role = 'admin';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';
// require '../../../settings/koneksi.php';

$database = new Database();
$koneksi = $database->conn;

$query = mysqli_query($koneksi, "SELECT id_user, nama, email, username, role 
                                  FROM users 
                                  WHERE role = 'pengelola_ruangan' OR role = 'pengelola_lab' 
                                  ORDER BY nama ASC");
?>

<div class="container-xxl flex-grow-1 container-p-y">

  <?php
  // -----------------------------------------------------------------
  // BLOK INI SAYA HAPUS
  // -----------------------------------------------------------------
  // if (isset($_SESSION['pesan'])) {
  //   $pesan = $_SESSION['pesan'];
  //   echo '... (alert bootstrap) ...';
  //   unset($_SESSION['pesan']);
  // }
  // -----------------------------------------------------------------
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
                  <td>
                    <?= $no++; ?>
                  </td>

                  <td>
                    <i class="bx bxs-user fa-lg text-primary me-3"></i>
                    <strong><?= htmlspecialchars($data['nama']); ?></strong>
                  </td>

                  <td>
                    <i class="bx bx-envelope fa-lg text-info me-3"></i>
                    <?= htmlspecialchars($data['email']); ?>
                  </td>

                  <td>
                    <i class="bx bx-id-card fa-lg text-warning me-3"></i>
                    <?= htmlspecialchars($data['username']); ?>
                  </td>

                  <td>
                    <?php
                    $badge_class = ($data['role'] == 'pengelola_ruangan') ? 'bg-label-info' : 'bg-label-success';
                    $role_name = ($data['role'] == 'pengelola_ruangan') ? 'Pengelola Ruangan' : 'Pengelola Lab';
                    $role_icon_color = ($data['role'] == 'pengelola_ruangan') ? 'text-info' : 'text-success';
                    ?>
                    <i class="bx bx-shield-quarter fa-lg <?= $role_icon_color; ?> me-3"></i>
                    <span class="badge <?= $badge_class; ?>"><?= $role_name; ?></span>
                  </td>
                  
                  <td>
                    <div>
                      <a class="btn btn-warning btn-sm me-1" href="form_pengelola.php?id=<?= $data['id_user']; ?>">
                        <i class="bx bx-edit-alt me-1"></i> Edit
                      </a>

                      <a class="btn btn-danger btn-sm btn-delete" 
                         href="proses_pengelola.php?aksi=hapus&id=<?= $data['id_user']; ?>">
                        <i class="bx bx-trash me-1"></i> Delete
                      </a>
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
</div>

<script>
  // 1. Ambil semua tombol dengan class 'btn-delete'
  const deleteButtons = document.querySelectorAll('.btn-delete');

  // 2. Loop semua tombol yang ditemukan
  deleteButtons.forEach(button => {
    
    // 3. Tambahkan event listener 'click' untuk setiap tombol
    button.addEventListener('click', function (event) {
        
        // 4. Hentikan aksi default dari link
        event.preventDefault(); 
        
        // 5. Ambil URL hapus dari atribut 'href'
        const deleteUrl = this.href; 

        // 6. Tampilkan konfirmasi SweetAlert
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang sudah dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            // 7. Jika pengguna menekan "Ya, hapus!"
            if (result.isConfirmed) {
                // Arahkan browser ke URL hapus
                window.location.href = deleteUrl;
            }
        });
    });
  });
</script>


<?php
// -----------------------------------------------------------------
// BLOK PHP ANDA UNTUK SWEETALERT SESSION (INI SUDAH BENAR)
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
    title: '" . addslashes($judul_alert) . "',
    text: '" . addslashes($pesan['isi']) . "',
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