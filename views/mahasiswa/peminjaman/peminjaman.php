<?php
$required_role = 'mahasiswa';

require '../../../partials/mahasiswa/header.php';
require '../../../partials/mahasiswa/sidebar.php';
require '../../../partials/mahasiswa/navbar.php';

if (!isset($koneksi)) {
    require '../../../settings/koneksi.php';
    $db = new Database();
    $koneksi = $db->conn;
}

$base_url = 'peminjaman.php';
$tipe_entitas = isset($_GET['tipe']) ? $_GET['tipe'] : 'ruangan'; // Default: ruangan
$entitas_id_selected = isset($_GET['id_entitas']) ? (int)$_GET['id_entitas'] : 0;

if ($tipe_entitas == 'ruangan') {
    $tabel_entitas = 'ruangan';
    $kolom_id = 'id_ruangan';
    $kolom_nama = 'nama_ruangan';
    $kolom_status = 'status_ruangan';
    $tabel_fasilitas_junction = 'ruangan_fasilitas';
    $tabel_jadwal = 'jadwal_ruangan';
    $id_fasilitas_fk = 'ruangan_id';
    $id_jadwal_fk = 'ruangan_id';
    $nama_entitas_kapital = 'Ruangan';
    $folder_gambar = 'ruangan';
    $proses_form = 'proses_peminjaman_ruangan.php';
} elseif ($tipe_entitas == 'laboratorium') {
    $tabel_entitas = 'laboratorium';
    $kolom_id = 'id_lab';
    $kolom_nama = 'nama_lab';
    $kolom_status = 'status_lab';
    $tabel_fasilitas_junction = 'lab_fasilitas';
    $tabel_jadwal = 'jadwal_lab';
    $id_fasilitas_fk = 'lab_id';
    $id_jadwal_fk = 'lab_id';
    $nama_entitas_kapital = 'Laboratorium';
    $folder_gambar = 'lab';
    $proses_form = 'proses_peminjaman_lab.php';
} else {
    // Jika tipe tidak valid, reset ke default
    $tipe_entitas = 'ruangan';
    header("Location: {$base_url}?tipe={$tipe_entitas}");
    exit();
}

// --- 3. Ambil Daftar Semua Entitas (TANPA DUMMY SISTEM) ---
$sql_list = "SELECT {$kolom_id}, {$kolom_nama} 
             FROM {$tabel_entitas} 
             WHERE {$kolom_status} = 'tersedia' 
               AND {$kolom_id} > 0 
               AND {$kolom_nama} NOT LIKE '%SISTEM%'
             ORDER BY {$kolom_nama}";
$result_list = mysqli_query($koneksi, $sql_list);

$detail = null;
$events_json = '[]';

if ($entitas_id_selected > 0) {
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

    $sql_fasilitas = "
        SELECT f.nama_fasilitas, rf.jumlah
        FROM {$tabel_fasilitas_junction} rf
        INNER JOIN fasilitas f ON rf.fasilitas_id = f.id_fasilitas
        WHERE rf.{$id_fasilitas_fk} = $entitas_id_selected
    ";
    $result_fasilitas = mysqli_query($koneksi, $sql_fasilitas);

    $today = date('Y-m-d'); 
    
    $sql_jadwal = "
        SELECT tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, status_jadwal 
        FROM {$tabel_jadwal} 
        WHERE {$id_jadwal_fk} = $entitas_id_selected
        AND tanggal_selesai >= '{$today}' 
        ORDER BY tanggal_mulai
    ";
    $result_jadwal = mysqli_query($koneksi, $sql_jadwal);

    $events = [];
    while ($row = mysqli_fetch_assoc($result_jadwal)) {
        // Logika pewarnaan berdasarkan status
        $color = match ($row['status_jadwal']) {
            'Perbaikan', 'Diblokir' => '#dc3545', // Merah (Keras/Primary Danger)
            'Dipakai' => '#ffc107',              // Kuning (Sedang Digunakan/Warning)
            'Menunggu' => '#0d6efd',             // Biru (Sedang Diproses/Primary Info) 
            default => '#198754',                 // Hijau (Fallback/Success)
        };

        // FullCalendar memerlukan format YYYY-MM-DDTTHH:MM:SS
        $events[] = [
            'title' => strtoupper($row['status_jadwal']) . " ({$row['jam_mulai']} - {$row['jam_selesai']})",
            'start' => $row['tanggal_mulai'] . 'T' . $row['jam_mulai'],
            'end' => $row['tanggal_selesai'] . 'T' . $row['jam_selesai'],
            'backgroundColor' => $color,
            'borderColor' => $color,
            'status' => $row['status_jadwal']
        ];
    }
    $events_json = json_encode($events);
}
?>

<div class="container my-5">
    <h2 class="fw-bolder mb-3 text-dark"> Peminjaman: Cek Ketersediaan & Ajukan</h2>
    <p class="text-muted mb-4">Pilih jenis entitas, lalu pilih spesifik ruangan/lab untuk melihat detail fasilitas dan jadwal.</p>

    <div class="mb-5 border-bottom pb-3">
        <h3 class="fw-bold fs-5 mb-3 text-primary"><i class="bi bi-bookmark-fill me-2"></i> Tipe Peminjaman</h3>
        <div class="row g-3">
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= $base_url ?>?tipe=ruangan"
                    class="btn w-100 rounded-3 text-start shadow-sm py-3 px-4 <?= $tipe_entitas == 'ruangan' ? 'bg-primary text-white border-primary' : 'btn-outline-primary' ?> fw-semibold d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-house-door-fill me-2 fs-5"></i> Ruangan
                    </span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= $base_url ?>?tipe=laboratorium"
                    class="btn w-100 rounded-3 text-start shadow-sm py-3 px-4 <?= $tipe_entitas == 'laboratorium' ? 'bg-primary text-white border-primary' : 'btn-outline-primary' ?> fw-semibold d-flex align-items-center justify-content-between">
                    <span>
                        <i class="bi bi-tools me-2 fs-5"></i> Laboratorium
                    </span>
                    <i class="bi bi-chevron-right ms-auto"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="mb-5">
        <h3 class="fw-bold fs-5 mb-3 text-secondary"><i class="bi bi-grid-fill me-2"></i> Pilih <?= $nama_entitas_kapital ?></h3>
        <div class="row g-2">
            <?php 
            $entitas_list_count = mysqli_num_rows($result_list);
            mysqli_data_seek($result_list, 0); // Reset pointer
            $counter = 0;
            while ($entitas = mysqli_fetch_assoc($result_list)): 
                $counter++;
                $id = $entitas[$kolom_id];
                $nama = $entitas[$kolom_nama];
                $active = ($id == $entitas_id_selected);
                $btnClass = $active ? 'btn-primary shadow' : 'btn-outline-secondary';
            ?>
            <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                <a href="<?= $base_url ?>?tipe=<?= $tipe_entitas ?>&id_entitas=<?= $id ?>"
                    class="btn w-100 <?= $btnClass ?> rounded-3 py-2 fw-semibold text-truncate border-2"
                    title="<?= htmlspecialchars($nama) ?>">
                    <?= htmlspecialchars($nama) ?>
                </a>
            </div>
            <?php endwhile; ?>
        </div>
        <?php if ($entitas_list_count == 0): ?>
            <div class="alert alert-warning mt-3 text-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> Saat ini tidak ada <?= $nama_entitas_kapital ?> yang tersedia untuk dipinjam.
            </div>
        <?php endif; ?>
    </div>
    <?php if ($entitas_id_selected > 0 && $detail): ?>

        <hr class="my-5">

        <h3 class="fw-bold fs-4 mb-4 text-dark"><i class="bi bi-info-circle-fill text-info me-2"></i> Detail <?= htmlspecialchars($detail[$kolom_nama] ?? 'Entitas'); ?></h3>
        
        <div class="card border-0 shadow-lg mb-5 rounded-4 overflow-hidden">
            <div class="row g-0 align-items-stretch"> 
                
                <div class="col-lg-5 col-md-12 bg-dark d-flex align-items-center justify-content-center p-4">
                    <?php 
                        $image_path = (!empty($detail['gambar'])) 
                            ? '../../../assets/img/' . $folder_gambar . '/' . htmlspecialchars($detail['gambar'])
                            : '../../../assets/img/no-image.jpg';
                    ?>
                    <div class="w-100 p-2" style="max-width: 450px; margin: 0 auto; background: rgba(255, 255, 255, 0.1); border-radius: 10px;"> 
                        <img src="<?= $image_path; ?>"
                            class="img-fluid rounded-3 shadow"
                            alt="<?= htmlspecialchars($detail[$kolom_nama] ?? 'Gambar Entitas'); ?>"
                            style="width: 100%; height: auto; object-fit: contain; max-height: 500px;">
                    </div>
                </div>
                
                <div class="col-lg-7 col-md-12 bg-white">
                    <div class="card-body py-4 px-md-5">
                        <h4 class="card-title fw-bolder mb-4 text-primary border-bottom pb-2"><?= htmlspecialchars($detail[$kolom_nama] ?? ''); ?></h4>
                        
                        <div class="row mb-4">
                            <div class="col-sm-6 mb-3">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="d-block fw-light text-muted small">Kategori</span>
                                    <p class="mb-0 fw-semibold text-dark"><i class="bi bi-tag-fill text-danger me-2"></i> <?= htmlspecialchars($detail['nama_kategori'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="d-block fw-light text-muted small">Lokasi</span>
                                    <p class="mb-0 fw-semibold text-dark"><i class="bi bi-geo-alt-fill text-warning me-2"></i> <?= htmlspecialchars($detail['lokasi'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="d-block fw-light text-muted small">Kapasitas</span>
                                    <p class="mb-0 fw-semibold text-dark"><i class="bi bi-people-fill text-success me-2"></i> <?= htmlspecialchars($detail['kapasitas'] ?? 'N/A'); ?> orang</p>
                                </div>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <div class="p-3 bg-light rounded-3 border">
                                    <span class="d-block fw-light text-muted small">Status</span>
                                    <p class="mb-0 fw-semibold text-dark text-capitalize"><i class="bi bi-check-circle-fill text-info me-2"></i> <?= htmlspecialchars($detail['status_entitas'] ?? 'N/A'); ?></p>
                                </div>
                            </div>
                        </div>

                        <h5 class="fw-bold mt-4 mb-3 border-bottom pb-1 text-secondary"><i class="bi bi-tools me-2"></i> Fasilitas Tersedia</h5>
                        <ul class="list-unstyled row g-2">
                            <?php 
                            $fasilitas_found = false;
                            if (isset($result_fasilitas) && mysqli_num_rows($result_fasilitas) > 0): 
                                mysqli_data_seek($result_fasilitas, 0); 
                                while ($f = mysqli_fetch_assoc($result_fasilitas)): 
                                    $fasilitas_found = true;
                            ?>
                                <li class="col-lg-6 col-md-12">
                                    <span class="d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill text-primary me-2"></i> 
                                        <?= htmlspecialchars($f['nama_fasilitas']); ?> 
                                        <span class="small text-muted ms-auto"><?= $f['jumlah'] ? "({$f['jumlah']} unit)" : ''; ?></span>
                                    </span>
                                </li>
                            <?php 
                                endwhile; 
                            endif;
                            if (!$fasilitas_found):
                            ?>
                                <li class="col-12 text-muted fst-italic">Tidak ada fasilitas terdaftar untuk <?= $detail[$kolom_nama] ?? 'entitas ini'; ?>.</li>
                            <?php endif; ?>
                        </ul>

                    </div>
                </div>
            </div>
        </div>

        <h3 class="fw-bold fs-4 mb-4 text-dark"><i class="bi bi-calendar-check-fill text-danger me-2"></i> Pilih Tanggal Peminjaman</h3>
        <p class="text-muted">Klik pada tanggal yang kosong di kalender untuk mengajukan peminjaman. **Jadwal yang berwarna menandakan tanggal tersebut sudah terisi atau diblokir.**</p>
        
        <div id="calendar" class="border rounded-4 p-4 bg-white shadow-lg mb-5"></div>
        
        <div class="d-flex flex-wrap gap-4 justify-content-center mt-4 p-3 bg-light rounded-3 shadow-sm">
            <span class="badge bg-danger"><i class="bi bi-square-fill me-1"></i> Perbaikan/Diblokir</span>
            <span class="badge bg-warning text-dark"><i class="bi bi-square-fill me-1"></i> Dipakai/Disetujui</span>
            <span class="badge bg-primary"><i class="bi bi-square-fill me-1"></i> Menunggu Persetujuan</span>
            <span class="badge bg-success"><i class="bi bi-square-fill me-1"></i> Tersedia (Klik untuk Pinjam)</span>
        </div>

        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.11/locales/id.global.min.js"></script>

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
                    navLinks: true, 
                    editable: false,
                    dayMaxEvents: true, 
                    locale: 'id', 

                    // Logika ketika user KLIK TANGGAL
                    dateClick: function(info) {
                        const clickedDate = info.dateStr;
                        const now = new Date(); 
                        const todayISO = now.toISOString().slice(0, 10); 

                        // 1. Cek Tanggal Lampau
                        if (clickedDate < todayISO) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Tanggal Tidak Valid',
                                text: 'Tidak dapat meminjam untuk tanggal yang sudah berlalu.',
                                confirmButtonColor: '#dc3545'
                            });
                            return; 
                        }

                        // 2. Cek Konflik: Filter event yang masih AKTIF di tanggal ini
                        const conflictingEvents = eventsData.filter(event => {
                            const isSameDay = event.start.startsWith(clickedDate);
                            // Status yang memblokir: Perbaikan, Diblokir, Dipakai, Menunggu
                            const isBlocking = (event.status === 'Perbaikan' || event.status === 'Diblokir' || event.status === 'Dipakai' || event.status === 'Menunggu');
                            
                            if (isSameDay && isBlocking) {
                                if (clickedDate !== todayISO) {
                                    return true; 
                                }
                                
                                // Jika hari ini, cek jam
                                const eventEnd = new Date(event.end); 
                                const timeToleranceSeconds = 1; 
                                const eventEndTimeMinusTolerance = new Date(eventEnd.getTime() - (timeToleranceSeconds * 1000));
                                
                                if (eventEndTimeMinusTolerance > now) {
                                    return true; 
                                }
                                
                                return false; 
                            }

                            return false; 
                        });

                        // 3. Tampilkan Notifikasi Konflik atau Lanjut ke Form
                        if (conflictingEvents.length > 0) {
                            let conflictText = "<p class='text-danger fw-bold'>Tanggal ini sudah memiliki jadwal aktif yang memblokir:</p><ul>";
                            conflictingEvents.forEach(event => {
                                const timeMatch = event.title.match(/\((.*?)\)/);
                                const time = timeMatch ? timeMatch[1] : 'Waktu Tidak Diketahui';
                                conflictText += `<li><span class='badge bg-dark text-uppercase'>${event.status}</span> pada pukul ${time}</li>`;
                            });
                            conflictText += "</ul><p class='mt-3'>Silakan cek jam ketersediaan yang detail atau pilih tanggal lain.</p>";

                            Swal.fire({
                                icon: 'warning',
                                title: 'Jadwal Terisi!',
                                html: conflictText,
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            // Lanjut ke form_peminjaman.php
                            Swal.fire({
                                icon: 'question',
                                title: 'Konfirmasi Peminjaman',
                                text: `Anda akan mengajukan peminjaman untuk tanggal ${clickedDate}. Lanjutkan?`,
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Lanjutkan',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#0d6efd',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = `form_peminjaman.php?tipe=${tipeEntitas}&id_entitas=${entitasID}&tanggal=${clickedDate}`;
                                }
                            });
                        }
                    },

                    eventClick: function(info) {
                        const start = new Date(info.event.start);
                        const end = new Date(info.event.end);
                        Swal.fire({
                            title: 'Informasi Jadwal',
                            html: `
                                <table class="table table-sm text-start">
                                    <tr><th>Status</th><td>: <span class="badge bg-secondary text-uppercase">${info.event.extendedProps.status}</span></td></tr>
                                    <tr><th>Tanggal</th><td>: ${start.toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'})}</td></tr>
                                    <tr><th>Waktu</th><td>: ${start.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})} - ${end.toLocaleTimeString('id-ID', {hour: '2-digit', minute:'2-digit'})}</td></tr>
                                </table>
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
        <div class="alert alert-info border-0 p-4 shadow-sm text-center">
            <i class="bi bi-lightbulb-fill me-2"></i> Silakan pilih salah satu **<?= $nama_entitas_kapital ?>** di atas untuk melihat detail dan jadwal ketersediaannya.
        </div>
    <?php endif; ?>
</div>

<?php
require '../../../partials/mahasiswa/footer.php';
?>