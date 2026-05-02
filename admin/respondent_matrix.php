<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

$id = $_GET['id'];
$respondent = $pdo->prepare("SELECT * FROM respondents WHERE id = ?");
$respondent->execute([$id]);
$res_data = $respondent->fetch();

if (!$res_data) {
    header("Location: respondents.php");
    exit;
}

$criteria = $pdo->query("SELECT * FROM criteria ORDER BY code ASC")->fetchAll();
$pairs = [];
for ($i = 0; $i < count($criteria); $i++) {
    for ($j = $i + 1; $j < count($criteria); $j++) {
        $pairs[] = [$criteria[$i], $criteria[$j]];
    }
}

// Handle Save
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_matrix'])) {
    $pdo->prepare("DELETE FROM respondent_comparisons WHERE respondent_id = ?")->execute([$id]);
    foreach ($_POST['pair'] as $key => $val) {
        list($c1_id, $c2_id) = explode('-', $key);
        
        // Save C1 vs C2
        $stmt = $pdo->prepare("INSERT INTO respondent_comparisons (respondent_id, criteria_1, criteria_2, value) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $c1_id, $c2_id, $val]);
        
        // Save C2 vs C1 (Reciprocal)
        $stmt = $pdo->prepare("INSERT INTO respondent_comparisons (respondent_id, criteria_1, criteria_2, value) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id, $c2_id, $c1_id, 1/$val]);
    }
    header("Location: respondents.php?success=1");
    exit;
}

// Load existing
$existing_vals = [];
$existing = $pdo->prepare("SELECT * FROM respondent_comparisons WHERE respondent_id = ?");
$existing->execute([$id]);
foreach ($existing->fetchAll() as $e) {
    $existing_vals[$e['criteria_1'] . '-' . $e['criteria_2']] = $e['value'];
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Kuesioner: <?php echo $res_data['name']; ?></h1>
        <p class="text-gray-500">Pilih skala penilaian untuk setiap pasangan kriteria</p>
    </div>
    <a href="respondents.php" class="text-indigo-600 font-bold"><i class="fas fa-arrow-left mr-2"></i> Kembali</a>
</div>

<div class="space-y-6">
    <form action="" method="POST">
        <input type="hidden" name="save_matrix" value="1">
        
        <?php foreach ($pairs as $p): 
            $c1 = $p[0];
            $c2 = $p[1];
            $current_val = isset($existing_vals[$c1['id'] . '-' . $c2['id']]) ? $existing_vals[$c1['id'] . '-' . $c2['id']] : 1;
        ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="grid grid-cols-12 gap-4 items-center">
                <!-- Kriteria A -->
                <div class="col-span-3 text-right">
                    <span class="text-sm font-bold text-gray-700 block uppercase tracking-wider"><?php echo $c1['name']; ?></span>
                    <span class="text-[10px] text-indigo-500 font-bold"><?php echo $c1['code']; ?></span>
                </div>

                <!-- Scale -->
                <div class="col-span-6">
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-100">
                        <!-- Left Side (9-2) -->
                        <?php for ($v = 9; $v >= 2; $v--): ?>
                            <label class="flex flex-col items-center cursor-pointer group">
                                <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="<?php echo $v; ?>" 
                                    <?php echo (abs($current_val - $v) < 0.0001) ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                <span class="text-[10px] mt-1 font-bold text-gray-400 group-hover:text-indigo-600 transition"><?php echo $v; ?></span>
                            </label>
                        <?php endfor; ?>

                        <!-- Center (1) -->
                        <label class="flex flex-col items-center cursor-pointer group">
                            <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="1" 
                                <?php echo (abs($current_val - 1) < 0.0001) ? 'checked' : ''; ?>
                                class="w-5 h-5 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                            <span class="text-[10px] mt-1 font-bold text-indigo-600">1</span>
                        </label>

                        <!-- Right Side (2-9 reciprocal) -->
                        <?php for ($v = 2; $v <= 9; $v++): ?>
                            <label class="flex flex-col items-center cursor-pointer group">
                                <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="<?php echo 1/$v; ?>" 
                                    <?php echo (abs($current_val - (1/$v)) < 0.0001) ? 'checked' : ''; ?>
                                    class="w-4 h-4 text-indigo-600 bg-gray-100 border-gray-300 focus:ring-indigo-500 cursor-pointer">
                                <span class="text-[10px] mt-1 font-bold text-gray-400 group-hover:text-indigo-600 transition"><?php echo $v; ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Kriteria B -->
                <div class="col-span-3 text-left">
                    <span class="text-sm font-bold text-gray-700 block uppercase tracking-wider"><?php echo $c2['name']; ?></span>
                    <span class="text-[10px] text-indigo-500 font-bold"><?php echo $c2['code']; ?></span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mt-10 flex justify-center pb-12">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 px-20 rounded-2xl shadow-xl transition transform hover:-translate-y-1">
                Simpan Penilaian Responden
            </button>
        </div>
    </form>
</div>

<?php require_once '../layout/footer.php'; ?>
