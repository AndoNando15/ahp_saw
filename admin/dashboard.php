<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

// Get Stats
$totalAlternatives = $pdo->query("SELECT COUNT(*) FROM alternatives")->fetchColumn();
$totalCriteria = $pdo->query("SELECT COUNT(*) FROM criteria")->fetchColumn();
$latestResult = $pdo->query("SELECT * FROM ahp_results ORDER BY id DESC LIMIT 1")->fetch();
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
    <p class="text-gray-500">Ringkasan Sistem Pendukung Keputusan</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Total Alternatives -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
        <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl">
            <i class="fas fa-users"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-400 text-sm font-medium uppercase">Alternatif</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalAlternatives; ?></p>
        </div>
    </div>

    <!-- Total Criteria -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
        <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl">
            <i class="fas fa-list-check"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-400 text-sm font-medium uppercase">Kriteria</h3>
            <p class="text-2xl font-bold text-gray-800"><?php echo $totalCriteria; ?></p>
        </div>
    </div>

    <!-- AHP Status -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center">
        <div class="w-12 h-12 rounded-full <?php echo ($latestResult && $latestResult['cr'] < 0.1) ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?> flex items-center justify-center text-xl">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-gray-400 text-sm font-medium uppercase">AHP Status (CR)</h3>
            <p class="text-2xl font-bold text-gray-800">
                <?php echo $latestResult ? number_format($latestResult['cr'], 4) : 'N/A'; ?>
            </p>
            <span class="text-xs <?php echo ($latestResult && $latestResult['cr'] < 0.1) ? 'text-green-500' : 'text-red-500'; ?> font-semibold">
                <?php echo $latestResult ? ($latestResult['status'] == 'Consistent' ? 'Konsisten' : 'Tidak Konsisten') : 'Belum Ada Perhitungan'; ?>
            </span>
        </div>
    </div>
</div>

<!-- <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Website Kombinasi AHP dan SAW Calon Penerima BPNT Kelurahan Pekelingan</h3>
    <p class="text-gray-600 leading-relaxed">
        Sistem ini membantu Anda mengambil keputusan menggunakan metode AHP dan SAW. 
        Mulailah dengan mengelola **Kriteria**, kemudian input data **Alternatif**, lakukan **Perbandingan Berpasangan**, dan terakhir lihat **Ranking SAW** untuk melihat hasil akhir.
    </p>
    <div class="mt-6 flex space-x-4">
        <a href="criteria.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg transition">Kelola Kriteria</a>
        <a href="saw.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg transition">Lihat Ranking</a>
    </div>
</div> -->

<?php require_once '../layout/footer.php'; ?>
