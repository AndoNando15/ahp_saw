<?php
require_once '../config/connection.php';
require_once '../vendor/autoload.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

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
            if ($index == 0) continue; // Skip header
            
            // Mapping berdasarkan header: No., NAMA, NIK, KK, PENGHASILAN, USIA, PEKERJAAN, KATEGORI SASARAN
            $name = $row[1];
            $nik = $row[2];
            $kk = $row[3];
            $income_raw = strtoupper($row[4]);
            $age = $row[5];
            $occupation = $row[6];
            $target_category = $row[7];
            
            // Konversi penghasilan
            $income = 0;
            if ($income_raw == "TIDAK ADA") $income = 0;
            elseif ($income_raw == "1 JUTA") $income = 1000000;
            elseif ($income_raw == "> 2 JUTA") $income = 2000000;
            else $income = (float)$row[4];
            
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

$alternatives = $pdo->query("SELECT * FROM alternatives")->fetchAll();
$edit_data = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM alternatives WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_data = $stmt->fetch();
}
?>

<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Alternatif</h1>
        <p class="text-gray-500">Kelola data penerima bantuan (Manual & Import Excel)</p>
    </div>
    
    <div class="flex items-center space-x-4">
        <!-- Delete All Button -->
        <a href="?delete_all=1" onclick="return confirm('Apakah Anda yakin ingin menghapus SEMUA data alternatif?')" 
            class="bg-red-50 text-red-600 px-4 py-2 rounded-lg text-xs font-bold hover:bg-red-100 transition border border-red-200">
            <i class="fas fa-trash-alt mr-1"></i> Hapus Semua
        </a>

        <!-- Import Form -->
        <form action="" method="POST" enctype="multipart/form-data" class="flex items-center space-x-2 bg-white p-2 rounded-xl shadow-sm border border-gray-100">
            <input type="file" name="excel_file" accept=".xlsx, .xls" required class="text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-xs font-bold hover:bg-indigo-700 transition">
                <i class="fas fa-file-import mr-1"></i> Import
            </button>
        </form>
    </div>
</div>

<?php if ($import_msg): ?>
    <div class="mb-6 p-4 <?php echo strpos($import_msg, 'Error') !== false ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?> rounded-xl font-bold shadow-sm">
        <?php echo $import_msg; ?>
    </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Form Section -->
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-6"><?php echo $edit_data ? 'Edit' : 'Tambah'; ?> Alternatif</h3>
            <form action="" method="POST" class="space-y-4">
                <?php if ($edit_data): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required value="<?php echo $edit_data ? $edit_data['name'] : ''; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIK</label>
                        <input type="text" name="nik" required value="<?php echo $edit_data ? $edit_data['nik'] : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. KK</label>
                        <input type="text" name="kk" required value="<?php echo $edit_data ? $edit_data['kk'] : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Penghasilan (Rp)</label>
                    <input type="number" name="income" required value="<?php echo $edit_data ? $edit_data['income'] : ''; ?>"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Usia</label>
                        <input type="number" name="age" required value="<?php echo $edit_data ? $edit_data['age'] : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan</label>
                        <input type="text" name="occupation" required value="<?php echo $edit_data ? $edit_data['occupation'] : ''; ?>"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Target</label>
                    <select name="target_category" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="LANSIA" <?php echo ($edit_data && $edit_data['target_category'] == 'LANSIA') ? 'selected' : ''; ?>>LANSIA</option>
                        <option value="MISKIN EKTRIM" <?php echo ($edit_data && $edit_data['target_category'] == 'MISKIN EKTRIM') ? 'selected' : ''; ?>>MISKIN EKTRIM</option>
                        <option value="JANDA" <?php echo ($edit_data && $edit_data['target_category'] == 'JANDA') ? 'selected' : ''; ?>>JANDA</option>
                    </select>
                </div>
                <div class="pt-4 flex space-x-3">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-lg transition shadow-md">
                        Simpan
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="alternatives.php" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2 rounded-lg text-center transition">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama / NIK</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Penghasilan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Usia</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($alternatives as $alt): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800"><?php echo $alt['name']; ?></div>
                                <div class="text-xs text-gray-400"><?php echo $alt['nik']; ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">Rp <?php echo number_format($alt['income'], 0, ',', '.'); ?></td>
                            <td class="px-6 py-4 text-sm text-gray-600"><?php echo $alt['age']; ?> Thn</td>
                            <td class="px-6 py-4 text-center space-x-2">
                                <a href="?edit=<?php echo $alt['id']; ?>" class="text-indigo-600 hover:text-indigo-900 transition">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $alt['id']; ?>" onclick="return confirm('Hapus data ini?')" class="text-red-500 hover:text-red-700 transition">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
