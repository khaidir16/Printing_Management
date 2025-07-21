<?php
// help.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bantuan - PrintPro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white p-8 rounded-lg shadow">
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Pusat Bantuan</h1>
            <p class="text-gray-600">Halaman ini berisi dokumentasi, FAQ, dan cara menghubungi dukungan teknis.</p>
            <a href="index.php" class="mt-6 inline-block px-4 py-2 border rounded-lg">Kembali ke Dasbor</a>
        </div>
    </div>
</body>
</html>