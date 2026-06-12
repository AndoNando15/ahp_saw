<?php
require_once '../config/connection.php';

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

// Handle Edit Respondent Name and Role
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_respondent'])) {
    $newName = $_POST['name'] ?? $res_data['name'];
    $newRole = $_POST['role'] ?? ($res_data['role'] ?? '');
    $stmt = $pdo->prepare("UPDATE respondents SET name = ?, role = ? WHERE id = ?");
    $stmt->execute([$newName, $newRole, $id]);
    // Refresh data
    $respondent->execute([$id]);
    $res_data = $respondent->fetch();
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

<?php require_once '../layout/header.php'; ?>
<?php require_once '../layout/sidebar.php'; ?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-tight">Kuesioner: <?php echo $res_data['name']; ?></h1>
        <div class="relative bg-white/90 backdrop-blur-lg my-6 rounded-xl shadow-xl border border-gray-200 p-6 max-w-4xl mx-auto">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Edit Responden</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6" id="editForm">
        <input type="hidden" name="edit_respondent" value="1">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap Pakar</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($res_data['name']); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
            <input type="text" name="role" required value="<?php echo htmlspecialchars($res_data['role'] ?? ''); ?>" class="w-full px-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 transition" />
        </div>
        <div class="md:col-span-2 flex items-center space-x-4">
            <button type="submit" class="flex items-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl shadow-md transition transform hover:scale-105">
                <i class="fas fa-save mr-2"></i> Simpan Perubahan
            </button>
            <button type="button" onclick="document.getElementById('editForm').reset();" class="flex items-center bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-5 py-2 rounded-xl shadow-md transition transform hover:scale-105">
                <i class="fas fa-undo mr-2"></i> Reset
            </button>
        </div>
    </form>
</div>
        <p class="text-sm text-gray-500">Bandingkan kepentingan antar kriteria (Skala Saaty 1-9)</p>
    </div>
    <a href="respondents.php" class="bg-gray-100 text-gray-600 px-5 py-2.5 rounded-xl font-bold hover:bg-gray-200 transition text-sm flex items-center">
        <i class="fas fa-arrow-left mr-2"></i> Kembali
    </a>
</div>

<div class="space-y-6">
    <form action="" method="POST">
        <input type="hidden" name="save_matrix" value="1">
        
        <?php foreach ($pairs as $p): 
            $c1 = $p[0];
            $c2 = $p[1];
            $current_val = isset($existing_vals[$c1['id'] . '-' . $c2['id']]) ? $existing_vals[$c1['id'] . '-' . $c2['id']] : 1;
        ?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 md:p-8 overflow-hidden">
            <!-- Mobile Header Labels -->
            <div class="flex justify-between md:hidden mb-6">
                <div class="text-left w-1/2 pr-2">
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter"><?php echo $c1['code']; ?></span>
                    <h4 class="text-xs font-bold text-gray-800 leading-tight"><?php echo $c1['name']; ?></h4>
                </div>
                <div class="text-right w-1/2 pl-2">
                    <span class="text-[10px] font-black text-indigo-500 uppercase tracking-tighter"><?php echo $c2['code']; ?></span>
                    <h4 class="text-xs font-bold text-gray-800 leading-tight"><?php echo $c2['name']; ?></h4>
                </div>
            </div>

            <div class="flex flex-col md:flex-row md:items-center md:space-x-8">
                <!-- Desktop Left Label -->
                <div class="hidden md:block md:w-1/4 text-right">
                    <span class="text-xs font-black text-indigo-500 uppercase tracking-wider block mb-1"><?php echo $c1['code']; ?></span>
                    <h4 class="text-sm font-bold text-gray-700 uppercase"><?php echo $c1['name']; ?></h4>
                </div>

                <!-- Scale Container -->
                <div class="md:w-1/2">
                    <div class="relative py-4">
                        <!-- Connecting Line -->
                        <div class="absolute top-1/2 left-0 right-0 h-0.5 bg-gray-100 -translate-y-1/2 z-0"></div>
                        
                        <!-- Radio Options (Scrollable on Mobile) -->
                        <div class="relative z-10 flex items-center justify-between overflow-x-auto pb-4 md:pb-0 custom-scrollbar-mini">
                            <div class="flex items-center space-x-1 sm:space-x-2 md:space-x-0 md:justify-between w-full min-w-[500px] md:min-w-0">
                                
                                <!-- Left 9 to 2 -->
                                <?php for ($v = 9; $v >= 2; $v--): ?>
                                    <label class="flex flex-col items-center cursor-pointer group px-1">
                                        <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="<?php echo $v; ?>" 
                                            <?php echo (abs($current_val - $v) < 0.0001) ? 'checked' : ''; ?>
                                            class="w-5 h-5 text-indigo-600 border-2 border-gray-200 focus:ring-indigo-500 cursor-pointer">
                                        <span class="text-[9px] mt-2 font-bold text-gray-400 group-hover:text-indigo-600 transition"><?php echo $v; ?></span>
                                    </label>
                                <?php endfor; ?>

                                <!-- Center 1 -->
                                <label class="flex flex-col items-center cursor-pointer group px-2">
                                    <div class="p-1 rounded-full bg-indigo-50 border border-indigo-100 mb-1">
                                        <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="1" 
                                            <?php echo (abs($current_val - 1) < 0.0001) ? 'checked' : ''; ?>
                                            class="w-6 h-6 text-indigo-600 border-2 border-indigo-200 focus:ring-indigo-500 cursor-pointer">
                                    </div>
                                    <span class="text-[10px] font-black text-indigo-600">1</span>
                                </label>

                                <!-- Right 2 to 9 Reciprocal -->
                                <?php for ($v = 2; $v <= 9; $v++): ?>
                                    <label class="flex flex-col items-center cursor-pointer group px-1">
                                        <input type="radio" name="pair[<?php echo $c1['id']; ?>-<?php echo $c2['id']; ?>]" value="<?php echo 1/$v; ?>" 
                                            <?php echo (abs($current_val - (1/$v)) < 0.0001) ? 'checked' : ''; ?>
                                            class="w-5 h-5 text-indigo-600 border-2 border-gray-200 focus:ring-indigo-500 cursor-pointer">
                                        <span class="text-[9px] mt-2 font-bold text-gray-400 group-hover:text-indigo-600 transition"><?php echo $v; ?></span>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Legend Labels Mobile -->
                    <div class="flex justify-between mt-1 md:hidden">
                        <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest">← Lebih Penting</span>
                        <span class="text-[9px] font-bold text-indigo-400 uppercase tracking-widest">Lebih Penting →</span>
                    </div>
                </div>

                <!-- Desktop Right Label -->
                <div class="hidden md:block md:w-1/4 text-left">
                    <span class="text-xs font-black text-indigo-500 uppercase tracking-wider block mb-1"><?php echo $c2['code']; ?></span>
                    <h4 class="text-sm font-bold text-gray-700 uppercase"><?php echo $c2['name']; ?></h4>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="mt-12 flex justify-center pb-20">
            <button type="submit" class="w-full md:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 md:py-5 px-10 md:px-20 rounded-2xl shadow-2xl transition transform hover:-translate-y-1 text-sm md:text-lg">
                Simpan Penilaian Pakar
            </button>
        </div>
    </form>
</div>

<style>
.custom-scrollbar-mini::-webkit-scrollbar { height: 4px; }
.custom-scrollbar-mini::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
.custom-scrollbar-mini::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar-mini::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<?php require_once '../layout/footer.php'; ?>
