<?php
require '../partials/header_landing.php';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PinruLab JTI - Booking Ruangan</title>
    <link rel="stylesheet" href="../assets/css/home.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <main>
        <section class="hero">
            <div class="hero-content">
                <p>Welcome To PinruLab</p>
                <h1>Online Booking Ruangan dan Lab<br>Jurusan Teknologi Informasi</h1>
            </div>
            <div class="booking-form-container">
                <form class="booking-form">
                    <div class="form-group">
                        <label for="start-date">Tanggal Awal</label>
                        <input type="date" id="start-date">
                    </div>
                    <div class="form-group">
                        <label for="end-date">Tanggal Akhir</label>
                        <input type="date" id="end-date">
                    </div>
                    <div class="form-group">
                        <label for="room">Pilih Ruangan</label>
                        <select id="room">
                            <option value="">--- Pilih Ruangan ---</option>
                            <option value="lab-1">Lab RPL</option>
                            <option value="lab-2">Lab Jaringan</option>
                            <option value="ruang-rapat">Ruang Rapat</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-check">Check</button>
                </form>
            </div>
        </section>

        <section class="favorite-rooms">
            <div class="container">
                <h2>Ruangan Favorit</h2>
                <div class="card-grid">
                    <div class="card">
                        <div class="card-image"></div>
                        <div class="card-body">
                            <h3>Lorem Ipsum</h3>
                            <p>Lokasi: Lorem Ipsum Dolor</p>
                            <p>Kapasitas: xxx</p>
                            <p>Banyak Peminjaman: xxx</p>
                            <a href="#" class="btn btn-details">View Details</a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-image"></div>
                        <div class="card-body">
                            <h3>Lorem Ipsum</h3>
                            <p>Lokasi: Lorem Ipsum Dolor</p>
                            <p>Kapasitas: xxx</p>
                            <p>Banyak Peminjaman: xxx</p>
                            <a href="#" class="btn btn-details">View Details</a>
                        </div>
                    </div>
                    <div class="card card-highlighted">
                        <div class="card-image"></div>
                        <div class="card-body">
                            <h3>Lorem Ipsum</h3>
                            <p>Lokasi: Lorem Ipsum Dolor</p>
                            <p>Kapasitas: xxx</p>
                            <p>Banyak Peminjaman: xxx</p>
                            <a href="#" class="btn btn-details">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta">
            <div class="container cta-container">
                <div class="cta-text">
                    <p>More Info? Contact Us!</p>
                    <h2>+62-8123-4567-8901</h2>
                </div>
                <a href="#" class="btn btn-contact">Contact Us</a>
            </div>
        </section>
    </main>

</body>
<?php
require '../partials/footer.php';
?>
</html>