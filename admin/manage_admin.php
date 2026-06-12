<?php
require_once '../config/connection.php';
require_once '../layout/header.php';
require_once '../layout/sidebar.php';

// Handle CRUD actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username && $password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO admin (username, password) VALUES (?, ?)');
            $stmt->execute([$username, $hash]);
        }
    } elseif ($action === 'update') {
        $id = (int) ($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($id && $username) {
            if ($password) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('UPDATE admin SET username = ?, password = ? WHERE id = ?');
                $stmt->execute([$username, $hash, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE admin SET username = ? WHERE id = ?');
                $stmt->execute([$username, $id]);
            }
        }
    } elseif ($action === 'delete') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id) {
            $stmt = $pdo->prepare('DELETE FROM admin WHERE id = ?');
            $stmt->execute([$id]);
        }
    }
    // Refresh to reflect changes
    header('Location: manage_admin.php');
    exit;
}

// Fetch admin list
$admins = $pdo->query('SELECT id, username FROM admin ORDER BY id ASC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admin Accounts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#admin-table').DataTable({
                columnDefs: [{ orderable: false, targets: 2 }]
            });
        });
    </script>
</head>

<body class="bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Content area -->
        <div class="flex-1 p-8">
            <h1 class="text-2xl font-bold mb-6">Manajemen Akun Admin</h1>
            <button id="add-admin-btn" class="mb-4 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">
                <i class="fas fa-user-plus mr-2"></i>Tambah Admin
            </button>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white shadow rounded-lg">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Username</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($admins as $admin): ?>
                            <tr class="border-t">
                                <td class="px-4 py-2"><?= htmlspecialchars($admin['id']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($admin['username']) ?></td>
                                <td class="px-4 py-2 text-center space-x-2">
                                    <button class="edit-btn bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded"
                                        data-id="<?= $admin['id'] ?>"
                                        data-username="<?= htmlspecialchars($admin['username']) ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline need-confirm-form" data-confirm="Hapus admin ini?">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                        <button type="submit"
                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="admin-modal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <h2 id="modal-title" class="text-xl font-semibold mb-4">Tambah Admin</h2>
            <form id="admin-form" method="POST" class="space-y-4">
                <input type="hidden" name="action" id="form-action" value="create">
                <input type="hidden" name="id" id="admin-id" value="">
                <div>
                    <label class="block text-sm font-medium mb-1" for="username">Username</label>
                    <input type="text" name="username" id="username" required
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1" for="password">Password</label>
                    <input type="password" name="password" id="password"
                        class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan password bila tidak ingin mengubahnya (hanya untuk
                        edit).</p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" id="close-modal"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded">Simpan</button>
                </div>
            </form>
            <button id="modal-close-x" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
        const modal = document.getElementById('admin-modal');
        const addBtn = document.getElementById('add-admin-btn');
        const closeBtn = document.getElementById('close-modal');
        const closeX = document.getElementById('modal-close-x');
        const form = document.getElementById('admin-form');
        const modalTitle = document.getElementById('modal-title');
        const actionInput = document.getElementById('form-action');
        const idInput = document.getElementById('admin-id');
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const editButtons = document.querySelectorAll('.edit-btn');

        function openModal(mode, admin = {}) {
            modal.classList.remove('hidden');
            if (mode === 'edit') {
                modalTitle.textContent = 'Edit Admin';
                actionInput.value = 'update';
                idInput.value = admin.id;
                usernameInput.value = admin.username;
                passwordInput.value = '';
            } else {
                modalTitle.textContent = 'Tambah Admin';
                actionInput.value = 'create';
                idInput.value = '';
                usernameInput.value = '';
                passwordInput.value = '';
            }
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        addBtn.addEventListener('click', () => openModal('create'));
        closeBtn.addEventListener('click', closeModal);
        closeX.addEventListener('click', closeModal);

        editButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const admin = {
                    id: btn.dataset.id,
                    username: btn.dataset.username
                };
                openModal('edit', admin);
            });
        });
    </script>
</body>

</html>