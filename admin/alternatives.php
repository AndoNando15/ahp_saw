<?php
require_once '../config/connection.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$import_msg = "";

// Handle Delete
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM alternatives WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: alternatives.php");
    exit;
}

// Handle Delete All
if (isset($_GET['delete_all'])) {
    $pdo->query("TRUNCATE TABLE alternatives");
    header("Location: alternatives.php");
    exit;
}

// Handle Import Excel
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['excel_file'])) {
    try {
        $file = $_FILES['excel_file']['tmp_name'];
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        $count = 0;
        foreach ($data as $index => $row) {
            if ($index == 0)
                continue; // Skip header

            $name = $row[1];
            $nik = $row[2];
            $kk = $row[3];
            $income_raw = strtoupper($row[4]);
            $age = $row[5];
            $occupation = $row[6];
            $target_category = $row[7];

            $income = 0;
            if ($income_raw == "TIDAK ADA")
                $income = 0;
            elseif ($income_raw == "1 JUTA")
                $income = 1000000;
            elseif ($income_raw == "> 2 JUTA")
                $income = 2000000;
            else
                $income = (float) $row[4];

            $stmt = $pdo->prepare("INSERT INTO alternatives (name, nik, kk, income, age, occupation, target_category) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $nik, $kk, $income, $age, $occupation, $target_category]);
            $count++;
        }
        $import_msg = "Berhasil mengimpor $count data alternatif!";
    } catch (Exception $e) {
        $import_msg = "Error: " . $e->getMessage();
    }
}

// Handle Manual Add/Edit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['name'])) {
    $name = $_POST['name'];
    $nik = $_POST['nik'];
    $kk = $_POST['kk'];
    $income = $_POST['income'];
    $age = $_POST['age'];
    $occupation = $_POST['occupation'];
    $target_category = $_POST['target_category'];

    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE alternatives SET name=?, nik=?, kk=?, income=?, age=?, occupation=?, target_category=? WHERE id=?");
        $stmt->execute([$name, $nik, $kk, $income, $age, $occupation, $target_category, $_POST['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO alternatives (name, nik, kk, income, age, occupation, target_category) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $nik, $kk, $income, $age, $occupation, $target_category]);
    }
    header("Location: alternatives.php");
    exit;
}

$alternatives = $pdo->query("SELECT * FROM alternatives ORDER BY id DESC")->fetchAll();
$edit_data = null;
$show_modal = false;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM alternatives WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
    $show_modal = true;
}
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
?>

<div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
    <div>
        <h1 class="text-2xl font-bold text-gray-800 tracking-tight">Alternatif</h1>
        <p class="text-gray-500 text-sm">Total: <span
                class="font-bold text-indigo-600"><?php echo count($alternatives); ?></span> data penerima bantuan</p>
    </div>

    <div
        class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
        <button onclick="openModal()"
            class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100 flex items-center justify-center text-sm">
            <i class="fas fa-plus mr-2 text-xs"></i> Tambah Data
        </button>

        <a href="?delete_all=1"
            class="need-confirm bg-red-50 text-red-600 px-4 py-2.5 rounded-xl text-xs font-bold hover:bg-red-100 transition border border-red-200 text-center"
            data-confirm="Hapus SEMUA data?">
            <i class="fas fa-trash-alt mr-1"></i> Kosongkan
        </a>

        <form action="" method="POST" enctype="multipart/form-data"
            class="flex items-center space-x-2 bg-white p-1.5 rounded-xl shadow-sm border border-gray-100">
            <input type="file" name="excel_file" accept=".xlsx, .xls" required
                class="text-[10px] text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 w-full sm:w-auto">
            <button type="submit"
                class="bg-green-600 text-white px-4 py-2 rounded-lg text-[10px] font-bold hover:bg-green-700 transition flex-shrink-0">
                IMPORT
            </button>
        </form>
    </div>
</div>

<?php if ($import_msg): ?>
    <div
        class="mb-6 p-4 <?php echo strpos($import_msg, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?> rounded-xl font-bold shadow-sm text-sm">
        <?php echo $import_msg; ?>
    </div>
<?php endif; ?>

<!-- Table Section -->
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-x-auto">
    <table class="w-full text-left min-w-[1000px]">
        <thead class="bg-gray-50/50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">No</th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama / Identitas
                </th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Income
                </th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Age
                </th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Pekerjaan</th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kategori</th>
                <th class="px-6 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            <?php foreach ($alternatives as $i => $alt): ?>
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 text-xs font-bold text-gray-400"><?php echo $i + 1; ?></td>
                    <td class="px-6 py-4">
                        <div class="font-bold text-gray-800"><?php echo $alt['name']; ?></div>
                        <div class="text-[10px] text-gray-400 font-mono mt-1">N: <?php echo $alt['nik']; ?> | K:
                            <?php echo $alt['kk']; ?>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="text-sm font-bold text-indigo-600">Rp
                            <?php echo number_format($alt['income'], 0, ',', '.'); ?></span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span
                            class="px-2 py-1 bg-gray-100 rounded-lg text-[10px] font-bold text-gray-600"><?php echo $alt['age']; ?>
                            Thn</span>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-600"><?php echo $alt['occupation']; ?></td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 <?php
                        if ($alt['target_category'] == 'LANSIA')
                            echo 'bg-blue-100 text-blue-700';
                        elseif ($alt['target_category'] == 'MISKIN EKTRIM')
                            echo 'bg-red-100 text-red-700';
                        else
                            echo 'bg-purple-100 text-purple-700';
                        ?> rounded-full text-[10px] font-bold uppercase">
                            <?php echo $alt['target_category']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center space-x-2">
                            <a href="?edit=<?php echo $alt['id']; ?>"
                                class="w-8 h-8 rounded-lg bg-orange-50 text-orange-600 flex items-center justify-center hover:bg-orange-600 hover:text-white transition">
                                <i class="fas fa-edit text-xs"></i>
                            </a>
                            <a href="?delete=<?php echo $alt['id']; ?>"
                                class="need-confirm w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center hover:bg-red-600 hover:text-white transition"
                                data-confirm="Hapus data ini?">
                                <i class="fas fa-trash text-xs"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Form (Scrollable & Compact) -->
<div id="formModal"
    class="<?php echo $show_modal ? '' : 'hidden'; ?> fixed inset-0 bg-black/60 backdrop-blur-sm z-[100] flex items-center justify-center p-4">
    <div class="bg-white rounded-[2rem] w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
        <!-- Header -->
        <div class="bg-indigo-900 p-6 text-white flex justify-between items-center shrink-0">
            <div>
                <h3 class="text-lg font-bold"><?php echo $edit_data ? 'Edit Data' : 'Tambah Alternatif'; ?></h3>
                <p class="text-indigo-300 text-[10px] uppercase tracking-widest mt-1">Form Input Data Penerima</p>
            </div>
            <button onclick="closeModal()"
                class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center hover:bg-white/20 transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        <!-- Body (Scrollable) -->
        <div class="overflow-y-auto p-6 custom-scrollbar">
            <form id="altForm" action="" method="POST" class="space-y-4">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>

                <div>
                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Nama
                        Lengkap</label>
                    <input type="text" name="name" required value="<?php echo $edit_data ? $edit_data['name'] : ''; ?>"
                        placeholder="Contoh: Ahmad Sulaiman"
                        class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">NIK</label>
                        <input type="text" name="nik" required
                            value="<?php echo $edit_data ? $edit_data['nik'] : ''; ?>" placeholder="16 digit"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">No.
                            KK</label>
                        <input type="text" name="kk" required value="<?php echo $edit_data ? $edit_data['kk'] : ''; ?>"
                            placeholder="16 digit"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Usia</label>
                        <input type="number" name="age" required
                            value="<?php echo $edit_data ? $edit_data['age'] : ''; ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Penghasilan
                            (Rp)</label>
                        <input type="number" name="income" required
                            value="<?php echo $edit_data ? $edit_data['income'] : ''; ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pekerjaan</label>
                        <input type="text" name="occupation" required
                            value="<?php echo $edit_data ? $edit_data['occupation'] : ''; ?>"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm">
                    </div>
                    <div>
                        <label
                            class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Kategori
                            Target</label>
                        <select name="target_category" required
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-100 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition text-sm appearance-none">
                            <option value="LANSIA" <?php echo ($edit_data && $edit_data['target_category'] == 'LANSIA') ? 'selected' : ''; ?>>LANSIA</option>
                            <option value="MISKIN EKTRIM" <?php echo ($edit_data && $edit_data['target_category'] == 'MISKIN EKTRIM') ? 'selected' : ''; ?>>MISKIN EKTRIM
                            </option>
                            <option value="JANDA" <?php echo ($edit_data && $edit_data['target_category'] == 'JANDA') ? 'selected' : ''; ?>>JANDA</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="p-4 md:p-6 bg-gray-50 border-t border-gray-100 flex space-x-3 shrink-0">
            <button type="submit" form="altForm"
                class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 md:py-4 rounded-xl transition shadow-lg shadow-indigo-100 text-xs md:text-sm">
                <?php echo $edit_data ? 'Update Data' : 'Simpan Data'; ?>
            </button>
            <button type="button" onclick="closeModal()"
                class="flex-1 bg-white border border-gray-200 text-gray-500 font-bold py-3 md:py-4 rounded-xl hover:bg-gray-50 transition text-xs md:text-sm">Batal</button>
        </div>
    </div>
</div>

<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 5px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>

<script>
    function openModal() {
        document.getElementById('formModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('formModal').classList.add('hidden');
        if (window.location.search.includes('edit=')) {
            window.location.href = 'alternatives.php';
        }
    }
</script>

<?php require_once '../layout/footer.php'; ?>