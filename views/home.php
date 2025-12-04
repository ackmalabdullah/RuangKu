<?php
// Panggil file koneksi database
require '../settings/koneksi.php'; 

// Membuat objek database dan koneksi
$db = new Database(); 
$conn = $db->conn; 

// --- 1. Ambil Data Ruangan ---
$query_ruangan = "SELECT id_ruangan, nama_ruangan, lokasi, kapasitas, gambar FROM ruangan";
$result_ruangan = $conn->query($query_ruangan);
$total_ruangan = ($result_ruangan) ? $result_ruangan->num_rows : 0;

// --- 2. Ambil Data Lab ---
$query_lab = "SELECT id_lab, nama_lab, lokasi, kapasitas, gambar FROM laboratorium"; 
$result_lab = $conn->query($query_lab);
$total_lab = ($result_lab) ? $result_lab->num_rows : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RuangKU JTI - Booking Ruangan</title>
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php require '../partials/header_landing.php'; ?>

    <main>
        <!-- ===== HERO ===== -->
        <section class="hero" id="home">
            <div class="hero-content">
                <p>Welcome To RuangKU</p>
                <h1>Online Booking Ruangan dan Lab<br>Jurusan Teknologi Informasi</h1>
            </div>
        </section>

        <!-- ===== FITUR ===== -->
        <section class="features-box-section">
            <div class="container">
                <div class="features-grid">

                    <div class="feature-item">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Jadwal Real-time</h3>
                        <p>Lihat status ruangan dan lab terkini tanpa perlu konfirmasi.</p>
                    </div>

                    <div class="feature-item">
                        <i class="fas fa-bolt"></i>
                        <h3>Peminjaman Cepat</h3>
                        <p>Proses booking ruangan hanya dalam hitungan menit.</p>
                    </div>

                    <div class="feature-item">
                        <i class="fas fa-bell"></i>
                        <h3>Notifikasi Otomatis</h3>
                        <p>Dapatkan pemberitahuan persetujuan langsung via email/app.</p>
                    </div>

                    <div class="feature-item">
                        <i class="fas fa-chart-line"></i>
                        <h3>Laporan Riwayat</h3>
                        <p>Akses riwayat peminjaman Anda dengan mudah dan terstruktur.</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- ===== DAFTAR RUANGAN / LAB ===== -->
        <section class="room-list-section" id="daftar-ruangan">
            <div class="container">
                <h2 class="section-title">Daftar Ruangan dan Laboratorium</h2>

                <div class="room-categories">

                    <!-- Kategori Ruangan -->
                    <div class="category-item" data-target="ruangan">
                        <img src="../assets/img/logo_ruangan.png" alt="Logo Ruangan" class="category-icon">
                        <div class="category-info">
                            <h3>Ruangan</h3>
                            <p id="total-ruangan"><?php echo $total_ruangan; ?> Ruangan Tersedia</p>
                        </div>
                    </div>

                    <!-- Kategori Lab -->
                    <div class="category-item" data-target="lab">
                        <img src="../assets/img/logo_lab.png" alt="Logo Lab" class="category-icon">
                        <div class="category-info">
                            <h3>Laboratorium</h3>
                            <p id="total-lab"><?php echo $total_lab; ?> Lab Tersedia</p>
                        </div>
                    </div>

                </div>

                <!-- DISPLAY KONTEN -->
                <div class="content-display">


                    <!-- ==== RUANGAN ==== -->
                    <div id="ruangan" class="room-content">
                        <div class="card-grid">
                            <?php
                            if ($result_ruangan && $result_ruangan->num_rows > 0) {
                                while ($room = $result_ruangan->fetch_assoc()) { ?>
                                    <div class="room-card">
                                        <div class="card-image-placeholder">
                                            <img src="../assets/img/ruangan/<?php echo htmlspecialchars($room['gambar']); ?>" 
                                                 alt="<?php echo htmlspecialchars($room['nama_ruangan']); ?>">
                                        </div>
                                        <div class="card-body">
                                            <h3><?php echo htmlspecialchars($room['nama_ruangan']); ?></h3>
                                            <p><i class="fas fa-map-marker-alt"></i> Lokasi: <?php echo htmlspecialchars($room['lokasi']); ?></p>
                                            <p><i class="fas fa-users"></i> Kapasitas: <?php echo htmlspecialchars($room['kapasitas']); ?> Orang</p>
                                        </div>
                                    </div>
                            <?php }
                            } else {
                                echo "<p style='text-align:center;grid-column:1/-1;'>Tidak ada ruangan yang tersedia saat ini.</p>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- ==== LAB ==== -->
                    <div id="lab" class="room-content">
                        <div class="card-grid">
                            <?php
                            if ($result_lab && $result_lab->num_rows > 0) {
                                while ($lab = $result_lab->fetch_assoc()) { ?>
                                    <div class="room-card">
                                        <div class="card-image-placeholder">
                                            <img src="../assets/img/lab/<?php echo htmlspecialchars($lab['gambar']); ?>" 
                                                 alt="<?php echo htmlspecialchars($lab['nama_lab']); ?>">
                                        </div>
                                        <div class="card-body">
                                            <h3><?php echo htmlspecialchars($lab['nama_lab']); ?></h3>
                                            <p><i class="fas fa-map-marker-alt"></i> Lokasi: <?php echo htmlspecialchars($lab['lokasi']); ?></p>
                                            <p><i class="fas fa-users"></i> Kapasitas: <?php echo htmlspecialchars($lab['kapasitas']); ?> Orang</p>
                                        </div>
                                    </div>
                            <?php }
                            } else {
                                echo "<p style='text-align:center;grid-column:1/-1;'>Tidak ada laboratorium yang tersedia saat ini.</p>";
                            }
                            ?>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ===== ALUR PEMINJAMAN ===== -->
        <section class="work-process-section" id="alur-peminjaman">
            <div class="container">
                <div class="section-header">
                    <h1>Alur Peminjaman</h1>
                    <p>Proses peminjaman ruangan dan lab yang mudah, cepat, dan transparan.</p>
                </div>

                <div class="process-boxes">
                    <div class="process-box">
                        <img src="../assets/img/work-process-item.png" alt="Langkah 1">
                        <strong>Ajukan Permohonan</strong>
                        <span>Mengisi formulir peminjaman secara online.</span>
                    </div>

                    <div class="process-box">
                        <img src="../assets/img/work-process-item.png" alt="Langkah 2">
                        <strong>Verifikasi</strong>
                        <span>Petugas memeriksa data dan jadwal.</span>
                    </div>

                    <div class="process-box">
                        <img src="../assets/img/work-process-item.png" alt="Langkah 3">
                        <strong>Persetujuan</strong>
                        <span>Pengguna mendapat konfirmasi peminjaman.</span>
                    </div>

                    <div class="process-box">
                        <img src="../assets/img/work-process-item.png" alt="Langkah 4">
                        <strong>Gunakan Ruangan</strong>
                        <span>Mengakses ruangan/lab sesuai jadwal.</span>
                    </div>

                    <div class="process-box">
                        <img src="../assets/img/work-process-item.png" alt="Langkah 5">
                        <strong>Selesai & Feedback</strong>
                        <span>Memberikan penilaian untuk layanan.</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== CONTACT ===== -->
        <section class="contact-section" id="contact-us">
            <div class="contact-container">
                <h2 class="contact-title">Contact Us</h2>
                <p class="contact-tagline">Ada Pertanyaan? Hubungi Kami Sekarang</p>

                <form class="contact-form" onsubmit="kirimWA(); return false;">
                    <div class="input-group">
                        <label for="nama">Nama Lengkap</label>
                        <input type="text" id="nama" placeholder="Nama Lengkap" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" placeholder="Email" required>
                    </div>

                    <div class="input-group">
                        <label for="pesan">Pesan</label>
                        <textarea id="pesan" placeholder="Pesan Anda" required></textarea>
                    </div>

                    <div class="form-button">
                        <button type="submit" class="btn btn-check">Kirim Pesan</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <!-- JS -->
    <script>
    // ==== TAB RUANGAN / LAB ====
    document.addEventListener('DOMContentLoaded', function() {
        const categoryItems = document.querySelectorAll('.category-item');
        const contentAreas = document.querySelectorAll('.room-content');

        categoryItems.forEach(item => {
            item.addEventListener('click', function() {
                categoryItems.forEach(cat => cat.classList.remove('active'));
                contentAreas.forEach(content => content.classList.remove('active'));

                this.classList.add('active');
                const targetId = this.getAttribute('data-target');
                document.getElementById(targetId).classList.add('active');
            });
        });
    });

    // ==== WHATSAPP SEND ====
    function kirimWA() {
        let nama = document.getElementById("nama").value.trim();
        let email = document.getElementById("email").value.trim();
        let pesan = document.getElementById("pesan").value.trim();

        if (!nama || !email || !pesan) {
            alert("Harap isi semua field!");
            return;
        }

        let noWa = "6281233884767";
        let text = `Halo Admin RuangKu. Nama: ${nama}. Email: ${email}. Pesan: ${pesan}`;
        let url = "https://api.whatsapp.com/send?phone=" + noWa + "&text=" + encodeURIComponent(text);

        window.open(url, "_blank");
    }
    </script>

<div class="map-embed-container">
    <iframe
    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3949.4475411397284!2d113.72020707432857!3d-8.157583281724232!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dd695b6ea0e8375%3A0x4618d7137a4cf5c1!2sGedung%20Jurusan%20TI%20Politeknik%20Negeri%20Jember!5e0!3m2!1sid!2sid!4v1764794771317!5m2!1sid!2sid" 
    width="600" 
    height="450" 
    style="border:0;" 
        allowfullscreen=""
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade">
    </iframe>
</div>

    <?php require '../partials/footer.php'; ?>
</body>
</html>
