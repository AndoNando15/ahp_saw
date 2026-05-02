<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

// Get Criteria Weights from AHP
$weights_data = $pdo->query("SELECT cw.*, c.name, c.code FROM criteria_weights cw JOIN criteria c ON cw.criteria_id = c.id")->fetchAll();
$weights = [];
foreach ($weights_data as $w) {
    $weights[$w['code']] = $w['weight'];
}

// Get Alternatives
$alternatives = $pdo->query("SELECT * FROM alternatives")->fetchAll();

if (empty($weights) || empty($alternatives)) {
    echo "<div class='p-12 bg-white rounded-[2rem] shadow-sm border border-gray-100 text-center'>";
    echo "<div class='w-20 h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl'><i class='fas fa-exclamation-triangle'></i></div>";
    echo "<h2 class='text-2xl font-bold text-gray-800 mb-2'>Data Belum Lengkap</h2>";
    echo "<p class='text-gray-500 mb-8'>Harap lengkapi data kriteria (AHP) dan alternatif sebelum melakukan perhitungan SAW.</p>";
    echo "<div class='flex justify-center space-x-4'>";
    echo "<a href='ahp.php' class='bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition'>Proses AHP</a>";
    echo "<a href='alternatives.php' class='bg-gray-100 text-gray-700 px-6 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition'>Kelola Alternatif</a>";
    echo "</div>";
    echo "</div>";
    require_once '../layout/footer.php';
    exit;
}

/**
 * MAPPING LOGIC BASED ON USER SCALE IMAGE
 */
function mapIncome($val) {
    if ($val <= 0) return 5;
    if ($val <= 1000000) return 4;
    if ($val <= 2000000) return 3;
    return 1;
}

function mapAge($val) {
    if ($val >= 51) return 5;
    if ($val >= 46) return 4;
    if ($val >= 41) return 3;
    if ($val >= 36) return 2;
    if ($val >= 31) return 1;
    return 0;
}

function mapCategory($val) {
    $val = strtoupper(trim($val));
    if ($val == "LANSIA") return 5;
    if ($val == "MISKIN EKTRIM" || $val == "MISKIN EKSTREM") return 4;
    if ($val == "JANDA") return 3;
    return 1;
}

function mapOccupation($val) {
    $val = strtoupper(trim($val));
    if ($val == "MENGURUS RUMAH TANGGA") return 5;
    if ($val == "WIRASWASTA") return 4;
    if ($val == "PEKERJA SWASTA" || $val == "KARYAWAN SWASTA") return 3;
    return 1;
}

// Prepare data for normalization
$matrix = [];
foreach ($alternatives as $alt) {
    $matrix[$alt['id']] = [
        'C1' => mapIncome($alt['income']),
        'C2' => mapAge($alt['age']),
        'C3' => mapCategory($alt['target_category']),
        'C4' => mapOccupation($alt['occupation'])
    ];
}

// Find Max/Min for each criterion
$max_min = [];
foreach (['C1', 'C2', 'C3', 'C4'] as $code) {
    $vals = array_column($matrix, $code);
    if (count($vals) > 0) {
        $max_min[$code] = ['max' => max($vals), 'min' => min($vals)];
    } else {
        $max_min[$code] = ['max' => 1, 'min' => 1];
    }
}

// STEP 1: Normalization SAW (R_ij)
$normalized = [];
foreach ($matrix as $id => $row) {
    foreach ($row as $code => $val) {
        if ($code == 'C1' || $code == 'C4') {
            $normalized[$id][$code] = ($val == 0) ? 0 : $max_min[$code]['min'] / $val;
        } else {
            $normalized[$id][$code] = ($max_min[$code]['max'] == 0) ? 0 : $val / $max_min[$code]['max'];
        }
    }
}

// STEP 2: Ranking (V_i)
$results = [];
foreach ($alternatives as $alt) {
    $score = 0;
    foreach ($normalized[$alt['id']] as $code => $norm_val) {
        if (isset($weights[$code])) {
            $score += $weights[$code] * $norm_val;
        }
    }
    $results[] = [
        'id' => $alt['id'],
        'name' => $alt['name'],
        'nik' => $alt['nik'],
        'score' => $score,
        'raw' => $matrix[$alt['id']],
        'norm' => $normalized[$alt['id']]
    ];
}

usort($results, function($a, $b) {
    return $b['score'] <=> $a['score'];
});
?>

<div class="mb-8 flex justify-between items-end print:hidden">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Detail Perhitungan & Ranking SAW</h1>
        <p class="text-gray-500">Hasil pengolahan data alternatif menggunakan bobot kriteria AHP</p>
    </div>
    <button onclick="window.print()" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg">
        <i class="fas fa-print mr-2"></i> Cetak Laporan
    </button>
</div>

<div class="space-y-12 pb-20">
    <!-- 1. SKALA -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 overflow-x-auto print:shadow-none print:border-gray-200">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 text-sm font-black">1</span>
            Konversi Data ke Skala (Bobot Sub-Kriteria)
        </h3>
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-4 border border-gray-100 text-gray-400 font-bold uppercase text-[10px] tracking-widest">Alternatif</th>
                    <th class="p-4 border border-gray-100 text-center text-gray-400 font-bold uppercase text-[10px] tracking-widest">C1 (Income)</th>
                    <th class="p-4 border border-gray-100 text-center text-gray-400 font-bold uppercase text-[10px] tracking-widest">C2 (Age)</th>
                    <th class="p-4 border border-gray-100 text-center text-gray-400 font-bold uppercase text-[10px] tracking-widest">C3 (Target)</th>
                    <th class="p-4 border border-gray-100 text-center text-gray-400 font-bold uppercase text-[10px] tracking-widest">C4 (Occ)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $res): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 border-b border-gray-50 font-bold text-gray-800"><?php echo $res['name']; ?></td>
                        <td class="p-4 border-b border-gray-50 text-center font-mono"><?php echo $res['raw']['C1']; ?></td>
                        <td class="p-4 border-b border-gray-50 text-center font-mono"><?php echo $res['raw']['C2']; ?></td>
                        <td class="p-4 border-b border-gray-50 text-center font-mono"><?php echo $res['raw']['C3']; ?></td>
                        <td class="p-4 border-b border-gray-50 text-center font-mono"><?php echo $res['raw']['C4']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 2. NORMALISASI -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-8 overflow-x-auto print:shadow-none print:border-gray-200">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 text-sm font-black">2</span>
            Matriks Normalisasi (R)
        </h3>
        <p class="text-xs text-indigo-500 font-bold mb-4 uppercase tracking-tighter">* C1 & C4: COST | C2 & C3: BENEFIT</p>
        <table class="w-full text-left text-sm">
            <thead>
                <tr class="bg-gray-50">
                    <th class="p-4 border border-gray-100 text-gray-400 font-bold uppercase text-[10px] tracking-widest">Alternatif</th>
                    <th class="p-4 border border-gray-100 text-center text-indigo-600 font-bold uppercase text-[10px] tracking-widest">C1</th>
                    <th class="p-4 border border-gray-100 text-center text-indigo-600 font-bold uppercase text-[10px] tracking-widest">C2</th>
                    <th class="p-4 border border-gray-100 text-center text-indigo-600 font-bold uppercase text-[10px] tracking-widest">C3</th>
                    <th class="p-4 border border-gray-100 text-center text-indigo-600 font-bold uppercase text-[10px] tracking-widest">C4</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $res): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 border-b border-gray-50 font-bold text-gray-700"><?php echo $res['name']; ?></td>
                        <?php foreach (['C1','C2','C3','C4'] as $c): ?>
                            <td class="p-4 border-b border-gray-50 text-center font-mono text-gray-500">
                                <?php echo number_format($res['norm'][$c], 4); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 3. RANKING -->
    <div class="bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-8 print:shadow-none print:border-gray-200">
        <h3 class="text-xl font-bold text-gray-800 mb-8 flex items-center">
            <span class="w-8 h-8 rounded-lg bg-yellow-400 text-white flex items-center justify-center mr-3 text-sm font-black">3</span>
            Hasil Perankingan Akhir
        </h3>
        <table class="w-full text-left">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="p-5 font-bold uppercase text-xs tracking-widest rounded-tl-3xl">Rank</th>
                    <th class="p-5 font-bold uppercase text-xs tracking-widest">Nama Alternatif</th>
                    <th class="p-5 font-bold uppercase text-xs tracking-widest">NIK</th>
                    <th class="p-5 font-bold uppercase text-xs tracking-widest text-right rounded-tr-3xl">Skor Akhir (V)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($results as $index => $res): ?>
                    <tr class="<?php echo $index < 3 ? 'bg-indigo-50/30' : ''; ?> hover:bg-gray-50 transition">
                        <td class="p-5">
                            <?php if ($index == 0): ?>
                                <span class="w-10 h-10 rounded-xl bg-yellow-400 text-white flex items-center justify-center font-black shadow-lg">1</span>
                            <?php elseif ($index == 1): ?>
                                <span class="w-10 h-10 rounded-xl bg-gray-300 text-white flex items-center justify-center font-black shadow-lg">2</span>
                            <?php elseif ($index == 2): ?>
                                <span class="w-10 h-10 rounded-xl bg-orange-300 text-white flex items-center justify-center font-black shadow-lg">3</span>
                            <?php else: ?>
                                <span class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100"><?php echo $index + 1; ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="p-5">
                            <div class="font-bold text-gray-800 text-lg"><?php echo $res['name']; ?></div>
                        </td>
                        <td class="p-5 text-gray-400 font-mono text-sm"><?php echo $res['nik']; ?></td>
                        <td class="p-5 text-right">
                            <div class="inline-block bg-indigo-700 text-white font-mono font-black px-5 py-2 rounded-2xl shadow-lg shadow-indigo-100">
                                <?php echo number_format($res['score'], 4); ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .print\:hidden { display: none !important; }
    body { background: white !important; }
    .sidebar { display: none !important; }
    .main-content { margin-left: 0 !important; padding: 0 !important; }
    table { page-break-inside: auto; }
    tr { page-break-inside: avoid; page-break-after: auto; }
}
</style>

<?php require_once '../layout/footer.php'; ?>
