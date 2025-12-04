<?php
// Perhatikan: Data ($result_ruangan) HARUS sudah tersedia (disiapkan di index.php)
if ($result_ruangan && $result_ruangan->num_rows > 0) {
    while ($room = $result_ruangan->fetch_assoc()) {
?>

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

<?php 
    }
} else {
    echo "<p style='text-align: center; grid-column: 1 / -1;'>Tidak ada ruangan yang tersedia saat ini.</p>";
}
?>
