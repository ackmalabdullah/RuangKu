<?php
$base_path = "/APKPINRULAB";
// Panggil file koneksi atau pengaturan lain yang diperlukan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistika Pengguna - RuangKu</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="../assets/css/header_landing.css"> 
    <link rel="stylesheet" href="../assets/css/dashboard.css"> 
    <link rel="stylesheet" href="../assets/css/footer.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="dashboard-page">

    <?php include '../partials/header_landing.php'; ?>

    <main class="dashboard-page-content"> 
        <!-- Spacer untuk navbar fixed/absolute -->
        <div class="header-spacer"></div> 
        
        <!-- Dashboard Power BI -->
        <div class="dashboard-container">
            <iframe 
                title="RuangKu" 
                width="100%" 
                height="100%" 
                src="https://app.powerbi.com/view?r=eyJrIjoiNzJiZDZiZDUtYWFjOS00N2E2LWI0ZTYtNDUxNzQzYTg5YjA2IiwidCI6ImE2OWUxOWU4LWYwYTQtNGU3Ny1iZmY2LTk1NjRjODgxOWIxNCJ9" 
                frameborder="0" 
                allowFullScreen="true">
            </iframe>
        </div>
    </main>

    <?php include '../partials/footer.php'; ?>

</body>
</html>
