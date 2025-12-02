<?php
$required_role = 'mahasiswa';
require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

// Pastikan koneksi tersedia
if (!isset($koneksi)) {
    die("<div class='alert alert-danger'>Koneksi database tidak ditemukan.</div>");
}

// --- 1. Ambil Parameter dari URL ---
$base_url = 'peminjaman.php';
$tipe_entitas = isset($_GET['tipe']) ? $_GET['tipe'] : 'ruangan'; // Default: ruangan
$entitas_id_selected = isset($_GET['id_entitas']) ? (int)$_GET['id_entitas'] : 0;

// --- 2. Tentukan Variabel Berdasarkan Tipe Entitas ---
if ($tipe_entitas == 'ruangan') {
    $tabel_entitas = 'ruangan';
    $kolom_id = 'id_ruangan';
    $kolom_nama = 'nama_ruangan';
    $kolom_status = 'status_ruangan';
    $tabel_fasilitas_junction = 'ruangan_fasilitas';
    $tabel_jadwal = 'jadwal_ruangan';
    $id_fasilitas_fk = 'ruangan_id'; // FK di ruangan_fasilitas
    $id_jadwal_fk = 'ruangan_id'; // FK di jadwal_ruangan
    $nama_entitas_kapital = 'Ruangan';
    $folder_gambar = 'ruangan';
} elseif ($tipe_entitas == 'laboratorium') {
    $tabel_entitas = 'laboratorium';
    $kolom_id = 'id_lab';
    $kolom_nama = 'nama_lab';
    $kolom_status = 'status_lab';
    $tabel_fasilitas_junction = 'lab_fasilitas';
    $tabel_jadwal = 'jadwal_lab'; // SESUAIKAN
    $id_fasilitas_fk = 'lab_id';
    $id_jadwal_fk = 'lab_id'; // SESUAIKAN
    $nama_entitas_kapital = 'Laboratorium';
    $folder_gambar = 'lab';
} else {
    // Jika tipe tidak valid, reset ke default
    $tipe_entitas = 'ruangan';
    header("Location: {$base_url}?tipe={$tipe_entitas}");
    exit();
}


// --- 3. Ambil Daftar Semua Entitas (Ruangan atau Lab) ---
// --- 3. Ambil Daftar Semua Entitas (TANPA DUMMY SISTEM) ---
$sql_list = "SELECT {$kolom_id}, {$kolom_nama} 
             FROM {$tabel_entitas} 
             WHERE {$kolom_status} = 'tersedia' 
               AND {$kolom_id} > 0 
               AND {$kolom_nama} NOT LIKE '%SISTEM%'
             ORDER BY {$kolom_nama}";
$result_list = mysqli_query($koneksi, $sql_list);

// --- 4. Ambil Detail Entitas, Fasilitas, dan Jadwal (Jika Entitas dipilih) ---
$detail = null;
$events_json = '[]';

if ($entitas_id_selected > 0) {
    // a. Ambil detail entitas
    $sql_detail = "
        SELECT 
            r.{$kolom_nama},
            r.kapasitas,
            r.lokasi,
            r.gambar,
            r.{$kolom_status} AS status_entitas,
            k.nama_kategori
        FROM {$tabel_entitas} r
        LEFT JOIN kategori_ruangan k ON r.kategori_id = k.id_kategori
        WHERE r.{$kolom_id} = $entitas_id_selected
    ";
    $result_detail = mysqli_query($koneksi, $sql_detail);
    $detail = mysqli_fetch_assoc($result_detail);

    // b. Ambil daftar fasilitas
    $sql_fasilitas = "
        SELECT f.nama_fasilitas, rf.jumlah
        FROM {$tabel_fasilitas_junction} rf
        INNER JOIN fasilitas f ON rf.fasilitas_id = f.id_fasilitas
        WHERE rf.{$id_fasilitas_fk} = $entitas_id_selected
    ";
    $result_fasilitas = mysqli_query($koneksi, $sql_fasilitas);

    // c. Ambil data jadwal untuk Kalender
    $today = date('Y-m-d'); 
    
    $sql_jadwal = "
        SELECT tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, status_jadwal 
        FROM {$tabel_jadwal} 
        WHERE {$id_jadwal_fk} = $entitas_id_selected
        -- Tambahkan kondisi ini: Hanya ambil jadwal yang belum selesai (tanggal_selesai >= hari ini)
        AND tanggal_selesai >= '{$today}' 
        ORDER BY tanggal_mulai
    ";
    $result_jadwal = mysqli_query($koneksi, $sql_jadwal);

    $events = [];
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        // Logika pewarnaan berdasarkan statusg
        $color = match ($row['status_jadwal']) {
            'Perbaikan' => '#e74c3c',    // Merah (Blokir Keras)
            'Diblokir' => '#8e44ad',     // Ungu (Blokir Keras)
            'Dipakai' => '#f39c12',      // Oranye (Sedang Digunakan/Disetujui)
            'Menunggu' => '#3498db',     // BIRU (Jadwal diajukan & sedang diproses) 
            default => '#2ecc71',        // Hijau (Fallback)
        };

        // FullCalendar memerlukan format YYYY-MM-DDTTHH:MM:SS
        $events[] = [
            'title' => strtoupper($row['status_jadwal']) . " ({$row['jam_mulai']} - {$row['jam_selesai']})",
            'start' => $row['tanggal_mulai'] . 'T' . $row['jam_mulai'],
            'end' => $row['tanggal_selesai'] . 'T' . $row['jam_selesai'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'status' => $row['status_jadwal'] // Mempertahankan status
        ];
    }
    $events_json = json_encode($events);
}
?>

<div class="container my-5">
    <h2 class="fw-bold mb-4 text-primary">📚 Peminjaman: Pilih Jenis Ruangan</h2>

    <div class="d-flex gap-3 mb-4">
        <a href="<?= $base_url ?>?tipe=ruangan"
            class="btn <?= $tipe_entitas == 'ruangan' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-house-door-fill"></i> Ruangan
        </a>
        <a href="<?= $base_url ?>?tipe=laboratorium"
            class="btn <?= $tipe_entitas == 'laboratorium' ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-4 fw-bold shadow-sm">
            <i class="bi bi-tools"></i> Laboratorium
        </a>
    </div>

    <hr class="my-4">

    <h3 class="fw-bold mb-4 text-secondary">Pilih <?= $nama_entitas_kapital ?></h3>

    <div class="d-flex flex-wrap gap-2 mb-4">
        <?php while ($entitas = mysqli_fetch_assoc($result_list)): ?>
            <?php
            $id = $entitas[$kolom_id];
            $nama = $entitas[$kolom_nama];
            $active = ($id == $entitas_id_selected);
            $btnClass = $active ? 'btn-primary' : 'btn-outline-primary';
            ?>
            <a href="<?= $base_url ?>?tipe=<?= $tipe_entitas ?>&id_entitas=<?= $id ?>"
                class="btn <?= $btnClass ?> rounded-pill px-3 fw-semibold shadow-sm">
                <?= htmlspecialchars($nama) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <hr class="my-4">

    <?php if ($entitas_id_selected > 0 && $detail): ?>

        <div class="card border-0 shadow-lg mb-5 overflow-hidden">
            <div class="row g-0 align-items-stretch">
                <div class="col-md-5">
                    <?php if (!empty($detail['gambar'])): ?>
                        <img src="../../../assets/img/<?= $folder_gambar ?>/<?= htmlspecialchars($detail['gambar']); ?>"
                            class="img-fluid h-100 w-100 object-fit-cover"
                            alt="<?= htmlspecialchars($detail[$kolom_nama] ?? ''); ?>">
                    <?php else: ?>
                        <img src="../../../assets/img/no-image.jpg"
                            class="img-fluid h-100 w-100 object-fit-cover"
                            alt="No Image">
                    <?php endif; ?>
                </div>
                <div class="col-md-7 bg-light">
                    <div class="card-body py-4 px-5">
                        <h3 class="card-title fw-bold mb-3 text-dark"><?= htmlspecialchars($detail[$kolom_nama] ?? ''); ?></h3>
                        <p><i class="bi bi-tag-fill text-primary"></i> <b>Kategori:</b> <?= htmlspecialchars($detail['nama_kategori'] ?? ''); ?></p>
                        <p><i class="bi bi-geo-alt-fill text-danger"></i> <b>Lokasi:</b> <?= htmlspecialchars($detail['lokasi'] ?? 'N/A'); ?></p>
                        <p><i class="bi bi-people-fill text-success"></i> <b>Kapasitas:</b> <?= htmlspecialchars($detail['kapasitas'] ?? 'N/A'); ?> orang</p>
                        <p><i class="bi bi-check-circle-fill text-info"></i> <b>Status:</b> <?= htmlspecialchars($detail['status_entitas'] ?? 'N/A'); ?></p>

                        <p class="fw-semibold mt-4 mb-1"><i class="bi bi-tools text-warning"></i> Fasilitas:</p>
                        <ul class="mb-0">
                            <?php if (isset($result_fasilitas) && mysqli_num_rows($result_fasilitas) > 0): ?>
                                <?php while ($f = mysqli_fetch_assoc($result_fasilitas)): ?>
                                    <li><?= htmlspecialchars($f['nama_fasilitas']); ?> <?= $f['jumlah'] ? "({$f['jumlah']} unit)" : ''; ?></li>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <li><em>Tidak ada fasilitas terdaftar</em></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="fw-bold mb-3 text-primary">📅 Kalender Ketersediaan</h3>
        <div id="calendar" class="border rounded-4 p-4 bg-white shadow-sm"></div>

        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const eventsData = <?= $events_json ?>;
                const entitasID = <?= $entitas_id_selected ?>;
                const tipeEntitas = '<?= $tipe_entitas ?>'; 

                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: eventsData,
                    height: 'auto',
                    eventDisplay: 'block',
                    eventTextColor: '#fff',
                    selectable: true,

                    // Logika ketika user KLIK TANGGAL
                    dateClick: function(info) {
                        const clickedDate = info.dateStr;
                        const now = new Date(); // Dapatkan waktu dan tanggal saat ini
                        const todayISO = now.toISOString().slice(0, 10); // Format 'YYYY-MM-DD' hari ini

                        // 1. Cek Tanggal Lampau
                        if (clickedDate < todayISO) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tanggal Lampau',
                                text: 'Tidak dapat meminjam untuk tanggal yang sudah berlalu.',
                                confirmButtonColor: '#d33'
                            });
                            return; // Hentikan proses jika tanggal lampau
                        }

                        // 2. Cek Konflik: Filter event yang masih AKTIF di tanggal ini
                        const conflictingEvents = eventsData.filter(event => {
                            // a. Cek apakah tanggal event sama dengan tanggal yang diklik
                            const isSameDay = event.start.startsWith(clickedDate);
                            
                            // b. Cek apakah status event adalah yang memblokir
                            const isBlocking = (event.status === 'Perbaikan' || event.status === 'Diblokir' || event.status === 'Dipakai' || event.status === 'Menunggu');
                            
                            // Jika tanggalnya sama dan statusnya memblokir...
                            if (isSameDay && isBlocking) {
                                // Jika tanggal yang diklik BUKAN hari ini, event dianggap konflik penuh (seluruh hari diblokir)
                                if (clickedDate !== todayISO) {
                                    return true;
                                }
                                
                                // Jika tanggal yang diklik ADALAH hari ini, kita harus cek jam
                                // Kita harus membandingkan waktu selesai event dengan waktu sekarang (now)
                                const eventEnd = new Date(event.end); 
                                
                                // Event dianggap konflik HANYA jika waktu selesainya belum berlalu (eventEnd > now)
                                // Tambahkan sedikit toleransi waktu untuk memastikan event yang baru selesai 1 detik lalu tidak memblokir
                                const timeToleranceSeconds = 1; 
                                const eventEndTimeMinusTolerance = new Date(eventEnd.getTime() - (timeToleranceSeconds * 1000));
                                
                                if (eventEndTimeMinusTolerance > now) {
                                    return true; // Waktu selesai event masih di masa depan = Konflik Aktif
                                }
                                
                                // Jika waktu selesai event sudah <= waktu sekarang, artinya event sudah lewat, tidak dianggap konflik aktif.
                                return false; 
                            }

                            return false; // Bukan di hari yang sama atau status tidak memblokir
                        });

                        // 3. Tampilkan Notifikasi Konflik atau Lanjut ke Form
                        if (conflictingEvents.length > 0) {
                            let conflictText = "Tanggal ini sudah memiliki jadwal aktif:<ul>";
                            conflictingEvents.forEach(event => {
                                // Ambil jam dari event title (e.g., "DIPAKAI (08:00:00 - 10:00:00)")
                                const timeMatch = event.title.match(/\((.*?)\)/);
                                const time = timeMatch ? timeMatch[1] : 'Waktu Tidak Diketahui';

                                conflictText += `<li>**${event.status}** pada pukul ${time}</li>`;
                            });
                            conflictText += "</ul>Silakan cek jam ketersediaan atau pilih tanggal lain.";

                            Swal.fire({
                                icon: 'warning',
                                title: 'Jadwal Sudah Ada',
                                html: conflictText,
                                confirmButtonColor: '#d33'
                            });
                        } else {
                            // Lanjut ke form_peminjaman.php
                            window.location.href = `form_peminjaman.php?tipe=${tipeEntitas}&id_entitas=${entitasID}&tanggal=${clickedDate}`;
                        }
                    },

                    // Logika ketika user KLIK EVENT (opsional)
                    eventClick: function(info) {
                        Swal.fire({
                            title: 'Informasi Jadwal',
                            html: `
                                <b>Status:</b> ${info.event.extendedProps.status}<br>
                                <b>Waktu:</b> ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - 
                                ${info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                            `,
                            icon: 'info',
                            confirmButtonText: 'Tutup'
                        });
                    }
                });

                calendar.render();
            });
        </script>

    <?php else: ?>
        <div class="alert alert-info shadow-sm">
            Silakan pilih salah satu **<?= $nama_entitas_kapital ?>** di atas untuk melihat detail dan jadwal ketersediaannya.
        </div>
    <?php endif; ?>
</div>

<?php
require '../../../partials/mahasiswa/footer.php';
?>