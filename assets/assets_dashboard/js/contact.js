function kirimWA() {
    let nama = document.getElementById("nama").value;
    let pesan = document.getElementById("pesan").value;

    if (!nama || !pesan) {
        alert("Harap isi semua field!");
        return;
    }

    let noWa = "6281234567890";

    // Buat teks lengkap dulu
    let text = `Halo Admin RuangKu
Nama: ${nama}
Pesan: ${pesan}`;

    // Encode seluruh teks
    let encodedText = encodeURIComponent(text);

    // Buka WhatsApp
    window.open(`https://wa.me/${noWa}?text=${encodedText}`, "_blank");
}
