<?php
// Perhatikan: Data ($result_lab) HARUS sudah tersedia (disiapkan di index.php)
if ($result_lab && $result_lab->num_rows > 0) {
    // Pastikan variabel yang digunakan adalah $result_lab
    while ($lab = $result_lab->fetch_assoc()) {
?>

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

<?php 
    }
} else {
    echo "<p style='text-align: center; grid-column: 1 / -1;'>Tidak ada laboratorium yang tersedia saat ini.</p>";
}
?>
