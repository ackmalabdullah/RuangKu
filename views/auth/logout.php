<?php
session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Redirect kembali ke halaman login
header("Location: login.php?message=Anda telah berhasil logout.");
exit;
?>