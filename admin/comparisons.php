<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

$criteria = $pdo->query("SELECT * FROM criteria ORDER BY code ASC")->fetchAll();
$n = count($criteria);

// Get Aggregated Data
$comparisons = $pdo->query("SELECT * FROM comparisons")->fetchAll();
$comp_data = [];
foreach ($comparisons as $c) {
    $comp_data[$c['criteria_1']][$c['criteria_2']] = $c['value'];
}
?>

<div class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Matriks Perbandingan (Agregasi)</h1>
        <p class="text-gray-500">Hasil gabungan rata-rata geometrik (GEOMEAN) dari 15 responden</p>
    </div>
    <div class="flex space-x-3">
        <a href="respondents.php" class="bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition">
            <i class="fas fa-users mr-2"></i> Kelola Responden
        </a>
        <a href="ahp.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
            Lihat Detail Proses AHP <i class="fas fa-arrow-right ml-2"></i>
        </a>
    </div>
</div>

<?php if (isset($_GET['aggregated'])): ?>
    <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-r-xl animate-bounce">
        <p class="font-bold">Berhasil!</p>
        <p class="text-sm">Data dari seluruh responden telah berhasil diagregasi menggunakan rumus GEOMEAN.</p>
    </div>
<?php endif; ?>

<div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 overflow-x-auto">
    <table class="w-full border-collapse">
        <thead>
            <tr class="bg-gray-50">
                <th class="p-4 border border-gray-100 text-gray-400 font-semibold uppercase text-xs">Kriteria</th>
                <?php foreach ($criteria as $c): ?>
                    <th class="p-4 border border-gray-100 text-indigo-600 font-bold"><?php echo $c['code']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($criteria as $i => $c1): ?>
                <tr>
                    <th class="p-4 bg-gray-50 border border-gray-100 text-indigo-600 font-bold text-left"><?php echo $c1['code']; ?></th>
                    <?php foreach ($criteria as $j => $c2): ?>
                        <td class="p-4 border border-gray-100 text-center font-mono text-gray-600">
                            <?php 
                            if ($i == $j) {
                                echo "<span class='text-gray-300'>1.0000</span>";
                            } else {
                                $val = $comp_data[$c1['id']][$c2['id']] ?? 1;
                                echo number_format($val, 4);
                            }
                            ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-8 bg-indigo-50 border border-indigo-100 rounded-2xl p-6 flex items-center">
    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center flex-shrink-0">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="ml-4">
        <p class="text-indigo-900 font-bold">Informasi Agregasi</p>
        <p class="text-indigo-600 text-sm">Nilai di atas adalah hasil murni penggabungan data pakar. Untuk melihat normalisasi, bobot eigen, dan uji konsistensi, silakan lanjut ke halaman <strong>Hasil AHP</strong>.</p>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
