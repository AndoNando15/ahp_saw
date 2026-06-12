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
    ?>
    <div class="p-8 md:p-12 bg-white rounded-[2rem] shadow-sm border border-gray-100 text-center max-w-2xl mx-auto mt-10">
        <div
            class="w-16 h-16 md:w-20 md:h-20 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 text-2xl">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">Data Belum Lengkap</h2>
        <p class="text-sm md:text-base text-gray-500 mb-8">Harap lengkapi data kriteria (AHP) dan alternatif sebelum
            melakukan perhitungan SAW.</p>
        <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
            <a href="ahp.php"
                class="bg-indigo-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-indigo-700 transition text-sm">Proses
                AHP</a>
            <a href="alternatives.php"
                class="bg-gray-100 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-200 transition text-sm">Kelola
                Alternatif</a>
        </div>
    </div>
    <?php
    require_once '../layout/footer.php';
    exit;
}

/**
 * MAPPING LOGIC
 */
function mapIncome($val)
{
    if ($val <= 0)
        return 5;
    if ($val <= 1000000)
        return 4;
    if ($val <= 2000000)
        return 3;
    return 1;
}

function mapAge($val)
{
    if ($val >= 51)
        return 5;
    if ($val >= 46)
        return 4;
    if ($val >= 41)
        return 3;
    if ($val >= 36)
        return 2;
    if ($val >= 31)
        return 1;
    return 0;
}

function mapCategory($val)
{
    $val = strtoupper(trim($val));
    if ($val == "LANSIA")
        return 5;
    if ($val == "MISKIN EKTRIM" || $val == "MISKIN EKSTREM")
        return 4;
    if ($val == "JANDA")
        return 3;
    return 1;
}

function mapOccupation($val)
{
    $val = strtoupper(trim($val));
    if ($val == "MENGURUS RUMAH TANGGA")
        return 5;
    if ($val == "WIRASWASTA")
        return 4;
    if ($val == "PEKERJA SWASTA" || $val == "KARYAWAN SWASTA")
        return 3;
    return 1;
}

// Prepare matrix
$matrix = [];
foreach ($alternatives as $alt) {
    $matrix[$alt['id']] = [
        'C1' => mapIncome($alt['income']),
        'C2' => mapAge($alt['age']),
        'C3' => mapCategory($alt['target_category']),
        'C4' => mapOccupation($alt['occupation'])
    ];
}

// Find Max/Min
$max_min = [];
foreach (['C1', 'C2', 'C3', 'C4'] as $code) {
    $vals = array_column($matrix, $code);
    $max_min[$code] = ['max' => max($vals ?: [1]), 'min' => min($vals ?: [1])];
}

// Normalization (R)
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

// Final Score (V)
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
        'norm' => $normalized[$alt['id']],
        'before' => [
            'C1' => $alt['income'],
            'C2' => $alt['age'],
            'C3' => $alt['target_category'],
            'C4' => $alt['occupation']
        ]
    ];
}

usort($results, function ($a, $b) {
    return $b['score'] <=> $a['score']; });
?>

<div
    class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-end space-y-4 md:space-y-0 print:hidden">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Ranking SAW</h1>
        <p class="text-sm text-gray-500">Hasil pengolahan data menggunakan bobot prioritas AHP</p>
    </div>
    <button onclick="window.print()"
        class="bg-indigo-600 text-white px-5 py-2 md:py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg w-full md:w-auto text-xs md:text-sm">
        <i class="fas fa-print mr-2 text-xs"></i> Cetak Laporan
    </button>
</div>

<div class="space-y-8 pb-20">
    <!-- 1. SKALA -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 md:p-8 overflow-x-auto">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span
                class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 text-sm font-black">1</span>
            Konversi Skala (Sub-Kriteria)
        </h3>
        <table class="w-full text-left min-w-[600px]">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Alternatif</th>
                    <?php foreach ($weights_data as $w): ?>
                        <th class="p-4 text-center text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <?php echo $w['code']; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($results as $res): ?>
                    <tr>
                        <td class="p-4 font-bold text-gray-800 text-sm"><?php echo $res['name']; ?></td>
                        <?php foreach (['C1', 'C2', 'C3', 'C4'] as $c):
                            $before = $res['before'][$c];
                            $after = $res['raw'][$c];
                            if ($c == 'C1') {
                                $before_fmt = 'Rp ' . number_format($before, 0, ',', '.');
                            } else {
                                $before_fmt = htmlspecialchars($before);
                            }
                            ?>
                            <td class="p-4 text-center font-mono text-sm"><?php echo $before_fmt; ?> &rarr;
                                <?php echo $after; ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>







    <!-- 2. NORMALISASI -->
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6 md:p-8 overflow-x-auto">
        <h3 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
            <span
                class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mr-3 text-sm font-black">2</span>
            Matriks Normalisasi (R)
        </h3>
        <table class="w-full text-left min-w-[600px]">
            <thead class="bg-gray-50/50">
                <tr>
                    <th class="p-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Alternatif</th>
                    <?php foreach (['C1', 'C2', 'C3', 'C4'] as $c): ?>
                        <th class="p-4 text-center text-[10px] font-black text-indigo-600 uppercase tracking-widest">
                            <?php echo $c; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($results as $res): ?>
                    <tr>
                        <td class="p-4 font-bold text-gray-700 text-sm"><?php echo $res['name']; ?></td>
                        <?php foreach (['C1', 'C2', 'C3', 'C4'] as $c): ?>
                            <td class="p-4 text-center font-mono text-sm text-gray-500">
                                <?php echo number_format($res['norm'][$c], 4); ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- 0. BOBOT KRITERIA SUMMARY (Moved: after Normalisasi) -->
    <div class="bg-indigo-900 rounded-[2rem] p-6 md:p-8 text-white shadow-xl mt-6">
        <h3 class="text-sm md:text-base font-bold mb-6 flex items-center opacity-80">
            <i class="fas fa-weight-hanging mr-3"></i> Bobot Prioritas Kriteria (Hasil AHP)
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <?php foreach ($weights_data as $w): ?>
                <div class="bg-white/10 p-4 rounded-2xl border border-white/5">
                    <span class="text-[10px] font-black uppercase opacity-60 block mb-1"><?php echo $w['code']; ?> -
                        <?php echo $w['name']; ?></span>
                    <span
                        class="text-xl md:text-2xl font-mono font-black"><?php echo number_format($w['weight'] * 100, 1); ?>%</span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- 3. RANKING -->
    <div class="bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-6 md:p-8 overflow-x-auto">
        <h3 class="text-lg md:text-xl font-bold text-gray-800 mb-8 flex items-center">
            <span
                class="w-8 h-8 rounded-lg bg-yellow-400 text-white flex items-center justify-center mr-3 text-sm font-black">3</span>
            Perankingan Akhir (V)
        </h3>
        <table class="w-full text-left min-w-[800px]">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="p-5 text-[10px] font-black uppercase tracking-widest rounded-tl-2xl">Rank</th>
                    <th class="p-5 text-[10px] font-black uppercase tracking-widest">Nama Alternatif</th>
                    <th class="p-5 text-[10px] font-black uppercase tracking-widest">Identitas (NIK)</th>
                    <th class="p-5 text-right text-[10px] font-black uppercase tracking-widest rounded-tr-2xl">Skor (V)
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($results as $idx => $res): ?>
                    <tr class="<?php echo $idx < 3 ? 'bg-indigo-50/20' : ''; ?> hover:bg-gray-50/50 transition">
                        <td class="p-5">
                            <?php if ($idx == 0): ?>
                                <div
                                    class="w-9 h-9 rounded-xl bg-yellow-400 text-white flex items-center justify-center font-black shadow-lg shadow-yellow-100">
                                    1</div>
                            <?php elseif ($idx == 1): ?>
                                <div
                                    class="w-9 h-9 rounded-xl bg-slate-300 text-white flex items-center justify-center font-black shadow-lg shadow-slate-100">
                                    2</div>
                            <?php elseif ($idx == 2): ?>
                                <div
                                    class="w-9 h-9 rounded-xl bg-amber-600/60 text-white flex items-center justify-center font-black shadow-lg shadow-amber-100">
                                    3</div>
                            <?php else: ?>
                                <div
                                    class="w-9 h-9 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center font-bold border border-gray-100 text-xs">
                                    <?php echo $idx + 1; ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="p-5 font-bold text-gray-800"><?php echo $res['name']; ?></td>
                        <td class="p-5 text-xs text-gray-400 font-mono"><?php echo $res['nik']; ?></td>
                        <td class="p-5 text-right">
                            <div
                                class="inline-block bg-indigo-700 text-white font-mono font-black px-4 py-2 rounded-xl shadow-lg shadow-indigo-100 text-sm">
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
        .print\:hidden {
            display: none !important;
        }

        body {
            background: white !important;
        }

        aside,
        header {
            display: none !important;
        }

        main {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .container {
            width: 100% !important;
            max-width: none !important;
            padding: 0 !important;
        }

        .rounded-[2rem],
        .rounded-3xl {
            border-radius: 0.5rem !important;
        }
    }
</style>

<?php require_once '../layout/footer.php'; ?>