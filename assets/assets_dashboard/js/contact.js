function kirimWA() {
    let nama = document.getElementById("nama").value.trim();
    let pesan = document.getElementById("pesan").value.trim();

    if (!nama || !pesan) {
        alert("Harap isi semua field!");
        return;
    }

    let noWa = "6281233884767"; // nomor tujuan tanpa + dan tanpa 0

    let text =
        "Halo Admin RuangKu%0A" +
        "Nama: " + nama + "%0A" +
        "Kritik/Saran: " + pesan;

    // FORMAT YANG BENAR
    let url = "https://api.whatsapp.com/send?phone=" + noWa + "&text=" + text;

    window.open(url, "_blank");
}
