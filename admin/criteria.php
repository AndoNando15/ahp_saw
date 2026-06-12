<?php
require_once '../config/connection.php';

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE criteria SET name = ? WHERE id = ?");
    $stmt->execute([$_POST['name'], $_POST['id']]);
    header("Location: criteria.php");
    exit;
}

// Include layout after handling POST
require_once '../layout/header.php';
require_once '../layout/sidebar.php';
$criteria = $pdo->query("SELECT * FROM criteria")->fetchAll();
?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800">Kriteria</h1>
    <p class="text-gray-500">Daftar kriteria penilaian untuk metode AHP</p>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="w-full text-left min-w-[600px]">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Kode</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Kriteria</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($criteria as $c): ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-xs font-bold"><?php echo $c['code']; ?></span>
                    </td>
                    <td class="px-6 py-4">
                        <form action="" method="POST" class="flex items-center space-x-2">
                            <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                            <input type="text" name="name" value="<?php echo $c['name']; ?>" required
                                class="bg-transparent border-none focus:ring-2 focus:ring-indigo-500 rounded px-2 py-1 w-full text-gray-700">
                            <button type="submit" class="text-green-500 hover:text-green-700 p-2 transition">
                                <i class="fas fa-check-circle"></i>
                            </button>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-center text-xs text-gray-400 italic">
                        Automatic
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="mt-8 bg-blue-50 border-l-4 border-blue-500 p-4 rounded max-w-4xl">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-500"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm text-blue-700">
                <strong>Tips:</strong> Klik pada nama kriteria untuk mengubahnya, lalu tekan tombol centang untuk menyimpan perubahan.
            </p>
        </div>
    </div>
</div>

<?php require_once '../layout/footer.php'; ?>
