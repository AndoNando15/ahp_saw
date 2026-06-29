<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

$criteria = $pdo->query("SELECT * FROM criteria ORDER BY code ASC")->fetchAll();
$n = count($criteria);
$RI = [0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45]; 

// STEP 1: Matriks perbandingan
$matrix = [];
foreach ($criteria as $c1) {
    foreach ($criteria as $c2) {
        if ($c1['id'] == $c2['id']) {
            $matrix[$c1['id']][$c2['id']] = 1;
        } else {
            $val = $pdo->prepare("SELECT value FROM comparisons WHERE criteria_1 = ? AND criteria_2 = ?");
            $val->execute([$c1['id'], $c2['id']]);
            $matrix[$c1['id']][$c2['id']] = $val->fetchColumn() ?: 1;
        }
    }
}

// Calculate row totals for original matrix
$row_totals = [];
foreach ($criteria as $c1) {
    $row_sum = 0;
    foreach ($criteria as $c2) {
        $row_sum += $matrix[$c1['id']][$c2['id']];
    }
    $row_totals[$c1['id']] = $row_sum;
}

// STEP 2: Jumlah tiap kolom (menggunakan Geomean dari sum masing-masing responden)
$col_totals = [];
$respondents = $pdo->query("SELECT id FROM respondents")->fetchAll(PDO::FETCH_COLUMN);
$num_resp = count($respondents);

if ($num_resp > 0) {
    foreach ($criteria as $c2) {
        $product = 1;
        foreach ($respondents as $resp_id) {
            $sum = 0;
            foreach ($criteria as $c1) {
                if ($c1['id'] == $c2['id']) {
                    $val = 1;
                } else {
                    $stmt = $pdo->prepare("SELECT value FROM respondent_comparisons WHERE respondent_id = ? AND criteria_1 = ? AND criteria_2 = ?");
                    $stmt->execute([$resp_id, $c1['id'], $c2['id']]);
                    $val = $stmt->fetchColumn() ?: 1;
                }
                $sum += $val;
            }
            $product *= $sum;
        }
        $col_totals[$c2['id']] = pow($product, 1 / $num_resp);
    }
} else {
    // Fallback if no respondents
    foreach ($criteria as $c2) {
        $sum = 0;
        foreach ($criteria as $c1) {
            $sum += $matrix[$c1['id']][$c2['id']];
        }
        $col_totals[$c2['id']] = $sum;
    }
}

// STEP 3: Normalisasi matriks
$norm_matrix = [];
foreach ($criteria as $c1) {
    foreach ($criteria as $c2) {
        $norm_matrix[$c1['id']][$c2['id']] = $matrix[$c1['id']][$c2['id']] / $col_totals[$c2['id']];
    }
}

// STEP 4: Hitung bobot prioritas
$weights = [];
$norm_row_sums = [];
foreach ($criteria as $c1) {
    $row_sum = 0;
    foreach ($criteria as $c2) {
        $row_sum += $norm_matrix[$c1['id']][$c2['id']];
    }
    $norm_row_sums[$c1['id']] = $row_sum;
    $weight = $row_sum / $n;
    $weights[$c1['id']] = $weight;
    
    // Save weight
    $pdo->query("DELETE FROM criteria_weights WHERE criteria_id = " . $c1['id']);
    $stmt = $pdo->prepare("INSERT INTO criteria_weights (criteria_id, weight) VALUES (?, ?)");
    $stmt->execute([$c1['id'], $weight]);
}

// STEP 5: λ max (Metode Total Kolom * Bobot)
$lambda_max = 0;
foreach ($criteria as $c) {
    $lambda_max += $col_totals[$c['id']] * $weights[$c['id']];
}

// STEP 6: CI dan CR
$CI = ($n > 1) ? ($lambda_max - $n) / ($n - 1) : 0;
$CR = ($n > 2) ? $CI / $RI[$n-1] : 0;
$status = ($CR < 0.1) ? "Consistent" : "Not Consistent";

// Save AHP result
$pdo->query("DELETE FROM ahp_results");
$stmt = $pdo->prepare("INSERT INTO ahp_results (lambda_max, ci, cr, status) VALUES (?, ?, ?, ?)");
$stmt->execute([$lambda_max, $CI, $CR, $status]);
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Laporan Analisis AHP</h1>
        <p class="text-sm text-gray-500">Hasil perhitungan bobot kriteria melalui 7 tahap ilmiah</p>
    </div>
    <div class="bg-indigo-600 text-white px-5 py-2 rounded-xl font-bold flex items-center shadow-lg text-sm">
        <i class="fas fa-check-circle mr-2"></i> Mode Verifikasi Aktif
    </div>
</div>

<div class="space-y-8 pb-10">
    <!-- Matrix Cards -->
    <div class="grid grid-cols-1 gap-8">
        <!-- STEP 1 & 2 -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <div class="flex items-center mb-8">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold mr-4">1</div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 leading-tight">Matriks Perbandingan Berpasangan</h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Step 1 & 2: Pairwise Comparison & Column Sum</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="p-4 text-left text-xs font-black text-gray-400 uppercase border-b border-gray-100">Kriteria</th>
                            <?php foreach ($criteria as $c): ?>
                                <th class="p-4 text-center text-indigo-600 font-bold border-b border-gray-100"><?php echo $c['code']; ?></th>
                            <?php endforeach; ?>
                            <th class="p-4 text-center text-indigo-700 font-black uppercase text-[10px] tracking-widest">Total Baris</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criteria as $c1): ?>
                            <tr>
                                <th class="p-4 bg-gray-50/30 text-indigo-600 font-bold text-left border-b border-gray-50"><?php echo $c1['code']; ?></th>
                                <?php foreach ($criteria as $c2): ?>
                                    <td class="p-4 text-center font-mono text-sm border-b border-gray-50">
                                        <?php echo number_format($matrix[$c1['id']][$c2['id']], 4); ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="p-4 text-center font-mono font-bold text-indigo-700 bg-indigo-100 border-b border-gray-50">
                                    <?php echo number_format($row_totals[$c1['id']], 4); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
<tfoot>
<tr class="bg-indigo-50/30">
<th class="p-4 text-indigo-700 font-black uppercase text-[10px] tracking-widest">Jumlah (Geomean)</th>
<?php foreach ($criteria as $c): ?>
<td class="p-4 text-center text-indigo-700 font-mono font-bold">
<?php echo number_format($col_totals[$c['id']], 4); ?>
</td>
<?php endforeach; ?>
<td class="p-4 text-center text-indigo-700 font-mono font-bold bg-indigo-100">
<?php echo number_format(array_sum($row_totals), 4); ?>
</td>
</tr>
</tfoot>
                   
                </table>
            </div>
        </div>

        <!-- STEP 3 & 4 -->
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <div class="flex items-center mb-8">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold mr-4">2</div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800 leading-tight">Matriks Normalisasi & Bobot Prioritas</h3>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Step 3 & 4: Normalization & Eigen Vector</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="p-4 text-left text-xs font-black text-gray-400 uppercase border-b border-gray-100">Kriteria</th>
                            <?php foreach ($criteria as $c): ?>
                                <th class="p-4 text-center text-indigo-600 font-bold border-b border-gray-100"><?php echo $c['code']; ?></th>
                            <?php endforeach; ?>
                            <th class="p-4 text-center bg-indigo-100 text-indigo-800 font-bold">JUMLAH</th>
                            <th class="p-4 text-center bg-indigo-600 text-white font-bold rounded-t-xl">BOBOT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($criteria as $c1): ?>
                            <tr>
                                <th class="p-4 bg-gray-50/30 text-indigo-600 font-bold text-left border-b border-gray-50"><?php echo $c1['code']; ?></th>
                                <?php foreach ($criteria as $c2): ?>
                                    <td class="p-4 text-center font-mono text-sm text-gray-400 border-b border-gray-50">
                                        <?php echo number_format($norm_matrix[$c1['id']][$c2['id']], 4); ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="p-4 text-center font-mono font-bold text-indigo-700 bg-indigo-50 border-b border-indigo-100">
                                    <?php echo number_format($norm_row_sums[$c1['id']], 4); ?>
                                </td>
                                <td class="p-4 text-center font-mono font-bold text-white bg-indigo-500 border-b border-indigo-400">
                                    <?php echo number_format($weights[$c1['id']], 4); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Final Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <div class="lg:col-span-1 bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8">
            <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-shield-alt mr-3 text-indigo-600"></i> Uji Konsistensi
            </h3>
            <div class="space-y-4">
                <div class="flex justify-between p-4 bg-gray-50 rounded-2xl">
                    <span class="text-xs font-bold text-gray-400 uppercase">Lambda Max</span>
                    <span class="font-mono font-bold text-gray-700"><?php echo number_format($lambda_max, 4); ?></span>
                </div>
                <div class="flex justify-between p-4 bg-gray-50 rounded-2xl">
                    <span class="text-xs font-bold text-gray-400 uppercase">CI</span>
                    <span class="font-mono font-bold text-gray-700"><?php echo number_format($CI, 4); ?></span>
                </div>
                <div class="flex justify-between p-4 bg-indigo-50 rounded-2xl border border-indigo-100">
                    <span class="text-xs font-bold text-indigo-400 uppercase">CR (Ratio)</span>
                    <span class="font-mono font-bold text-indigo-700"><?php echo number_format($CR, 4); ?></span>
                </div>
                <div class="mt-6 p-6 <?php echo $CR < 0.1 ? 'bg-green-500' : 'bg-red-500'; ?> rounded-2xl text-center text-white shadow-lg">
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70 mb-1">Status Akhir</p>
                    <p class="text-xl font-black italic"><?php echo $CR < 0.1 ? 'CONSISTENT' : 'INCONSISTENT'; ?></p>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 bg-indigo-900 rounded-[2rem] p-10 text-white relative overflow-hidden flex flex-col justify-between h-full shadow-2xl">
            <div class="relative z-10">
                <h3 class="text-2xl font-bold mb-4">Bobot Kriteria Berhasil Ditetapkan!</h3>
                <p class="text-indigo-200 leading-relaxed mb-8">Bobot prioritas kriteria dari hasil AHP (Multi-Expert) telah disimpan secara otomatis. Anda dapat melanjutkan ke proses perankingan alternatif menggunakan metode SAW sekarang.</p>
            </div>
            <div class="relative z-10 flex space-x-4">
                <a href="saw.php" class="flex-1 bg-white text-indigo-900 font-bold py-3 md:py-4 rounded-2xl text-center hover:bg-indigo-50 transition shadow-xl text-sm md:text-base">
                    Lanjut ke SAW <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
            <div class="absolute bottom-0 right-0 w-80 h-80 bg-white/5 rounded-full -mr-20 -mb-20"></div>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
