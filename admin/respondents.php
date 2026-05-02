<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

// Handle Add Respondent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO respondents (name) VALUES (?)");
    $stmt->execute([$name]);
    header("Location: respondents.php");
    exit;
}

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM respondents WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: respondents.php");
    exit;
}

$respondents = $pdo->query("SELECT * FROM respondents ORDER BY id ASC")->fetchAll();
$criteria = $pdo->query("SELECT * FROM criteria ORDER BY code ASC")->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-xl md:text-2xl font-bold text-gray-800">Manajemen Responden (Multi-Expert)</h1>
        <p class="text-sm text-gray-500">Kelola penilaian dari 15 pakar untuk pembobotan kriteria</p>
    </div>
    <div class="flex space-x-3 w-full md:w-auto">
        <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="flex-1 md:flex-none bg-indigo-600 text-white px-4 py-2 md:px-5 md:py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-200 text-xs md:text-sm">
            <i class="fas fa-plus mr-2"></i> Tambah Pakar
        </button>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 2xl:grid-cols-3 gap-6">
    <?php foreach ($respondents as $idx => $r): 
    $check = $pdo->prepare("SELECT COUNT(*) FROM respondent_comparisons WHERE respondent_id = ?");
    $check->execute([$r['id']]);
    $is_filled = $check->fetchColumn() > 0;
?>
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6 hover:shadow-xl transition-shadow duration-300">
            <div class="flex justify-between items-start mb-6">
                <div class="flex items-center">
                    <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg shadow-inner">
                        <?php echo $idx + 1; ?>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-800 leading-none mb-1"><?php echo $r['name']; ?></h3>
                        <?php if ($is_filled): ?>
                            <span class="text-[10px] font-black text-green-500 uppercase tracking-widest">● Terisi</span>
                        <?php else: ?>
                            <span class="text-[10px] font-black text-red-500 uppercase tracking-widest">○ Kosong</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex space-x-1">
                    <a href="respondent_matrix.php?id=<?php echo $r['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-indigo-50 hover:text-indigo-600 transition" title="Edit Matriks">
                        <i class="fas fa-edit text-sm"></i>
                    </a>
                    <a href="?delete=<?php echo $r['id']; ?>" onclick="return confirm('Hapus responden ini?')" class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 transition">
                        <i class="fas fa-trash text-sm"></i>
                    </a>
                </div>
            </div>

            <div class="bg-gray-50/50 rounded-2xl p-4 border border-gray-50">
                <table class="w-full text-[10px] border-collapse">
                    <thead>
                        <tr>
                            <th class="p-1"></th>
                            <?php foreach ($criteria as $c): ?>
                                <th class="p-1 font-bold text-gray-400 text-center"><?php echo $c['code']; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $comp_stmt = $pdo->prepare("SELECT * FROM respondent_comparisons WHERE respondent_id = ?");
                        $comp_stmt->execute([$r['id']]);
                        $r_comps = [];
                        foreach ($comp_stmt->fetchAll() as $cp) $r_comps[$cp['criteria_1']][$cp['criteria_2']] = $cp['value'];

                        foreach ($criteria as $c1): ?>
                            <tr>
                                <th class="p-1 font-bold text-indigo-600 text-left"><?php echo $c1['code']; ?></th>
                                <?php foreach ($criteria as $c2): ?>
                                    <td class="p-1 text-center text-gray-600">
                                        <?php 
                                        if ($c1['id'] == $c2['id']) echo "<span class='text-gray-300'>1</span>";
                                        else {
                                            $v = $r_comps[$c1['id']][$c2['id']] ?? 0;
                                            echo $v > 0 ? (round($v) == $v ? $v : number_format($v, 2)) : '-';
                                        }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="border-t border-gray-100">
                        <tr class="font-bold">
                            <td class="p-1 pt-2 text-gray-400">Total</td>
                            <?php foreach ($criteria as $c2): 
                                $col_sum = 0;
                                foreach ($criteria as $c1) {
                                    if ($c1['id'] == $c2['id']) $col_sum += 1;
                                    else $col_sum += ($r_comps[$c1['id']][$c2['id']] ?? 0);
                                }
                            ?>
                                <td class="p-1 pt-2 text-center text-indigo-600">
                                    <?php echo round($col_sum) == $col_sum ? $col_sum : number_format($col_sum, 1); ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="mt-12 bg-indigo-900 rounded-[2rem] p-6 md:p-10 text-white flex flex-col md:flex-row justify-between items-center shadow-2xl relative overflow-hidden">
    <div class="relative z-10 text-center md:text-left">
        <h3 class="text-xl md:text-2xl font-bold mb-2">Proses Agregasi Akhir</h3>
        <p class="text-sm text-indigo-200">Klik tombol di samping untuk menggabungkan seluruh data responden menggunakan rumus GEOMEAN.</p>
    </div>
    <a href="aggregate.php" class="relative z-10 mt-6 md:mt-0 bg-white text-indigo-900 px-6 py-2.5 md:px-10 md:py-4 rounded-2xl font-bold hover:scale-105 transition-transform shadow-xl text-xs md:text-base w-full md:w-auto text-center">
        Proses Agregasi Sekarang <i class="fas fa-sync-alt ml-2"></i>
    </a>
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full -mr-32 -mt-32"></div>
</div>

<!-- Modal Add -->
<div id="addModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
    <div class="bg-white rounded-[2rem] p-10 w-full max-w-md shadow-2xl">
        <h3 class="text-2xl font-bold text-gray-800 mb-6">Tambah Responden</h3>
        <form action="" method="POST">
            <div class="mb-8">
                <label class="block text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wider">Nama Lengkap Pakar</label>
                <input type="text" name="name" required placeholder="Contoh: Dr. Budi Santoso" class="w-full px-5 py-4 bg-gray-50 border-none rounded-2xl focus:ring-2 focus:ring-indigo-500 outline-none transition">
            </div>
            <div class="flex space-x-3">
                <button type="submit" class="flex-1 bg-indigo-600 text-white font-bold py-3 md:py-4 rounded-2xl hover:bg-indigo-700 transition text-sm">Simpan Data</button>
                <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 bg-gray-100 text-gray-500 font-bold py-3 md:py-4 rounded-2xl hover:bg-gray-200 transition text-sm">Batal</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
