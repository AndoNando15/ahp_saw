<!-- Sidebar -->
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>
<aside id="sidebar"
    class="w-full md:w-64 bg-indigo-900 text-white flex-shrink-0 shadow-xl fixed md:sticky md:top-0 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out h-full md:h-screen overflow-y-auto">
    <div class="p-6 flex items-center justify-center shrink-0 ">
        <h1 class="text-xl font-bold tracking-wider italic ">SI-AHAS</h1>
        <button class="md:hidden text-white focus:outline-none" id="close-menu-button">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>

    <nav class="mt-2 px-4 pb-8 space-y-6 overflow-y-auto">
        <!-- Group: Utama -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Master Data</p>
            <div class="space-y-1">
                <a href="dashboard.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'dashboard.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-tachometer-alt w-5 group-hover:text-indigo-300<?php echo $currentPage == 'dashboard.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Dashboard</span>
                </a>
                <a href="criteria.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'criteria.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-list-check w-5 group-hover:text-indigo-300<?php echo $currentPage == 'criteria.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Data Kriteria</span>
                </a>
                <a href="alternatives.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'alternatives.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-users w-5 group-hover:text-indigo-300<?php echo $currentPage == 'alternatives.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Data Alternatif</span>
                </a>
            </div>
        </div>

        <!-- Group: AHP -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Metode AHP</p>
            <div class="space-y-1">
                <a href="respondents.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'respondents.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-users-cog w-5 group-hover:text-indigo-300<?php echo $currentPage == 'respondents.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Kuesioner Pakar</span>
                </a>
                <a href="comparisons.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'comparisons.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-exchange-alt w-5 group-hover:text-indigo-300<?php echo $currentPage == 'comparisons.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Matriks Agregasi</span>
                </a>
                <a href="ahp.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'ahp.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-calculator w-5 group-hover:text-indigo-300<?php echo $currentPage == 'ahp.php' ? ' text-indigo-300' : ''; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Hasil Pembobotan</span>
                </a>
            </div>
        </div>

        <!-- Group: SAW -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Metode SAW</p>
            <div class="space-y-1">
                <a href="saw.php"
                    class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group<?php echo $currentPage == 'saw.php' ? ' bg-indigo-800' : ''; ?>">
                    <i
                        class="fas fa-trophy w-5 group-hover:text-indigo-300<?php echo $currentPage == 'saw.php' ? ' text-indigo-300' : ' text-yellow-400'; ?>"></i>
                    <span class="ml-3 text-sm font-normal">Ranking Akhir</span>
                </a>
            </div>
        </div>


    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden md:hidden"></div>

<!-- Main Content Area -->
<main class="flex-1 min-h-screen bg-gray-50/50 flex flex-col">
    <!-- Topbar -->
    <header
        class="bg-white/80 backdrop-blur-md shadow-sm py-4 px-4 md:px-8 flex justify-between items-center sticky top-0 z-30 border-b border-gray-100 shrink-0">
        <div class="flex items-center">
            <button
                class="md:hidden mr-4 text-indigo-900 focus:outline-none bg-indigo-50 w-10 h-10 rounded-xl flex items-center justify-center"
                id="mobile-menu-button">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <h2 class="text-sm md:text-base font-bold text-gray-700 tracking-tight">Website Kombinasi AHP dan SAW Calon
                Penerima BPNT Kelurahan Pekelingan</h2>
        </div>
        <div class="flex items-center space-x-2 md:space-x-4 relative" id="profile-menu-container">
            <div class="text-right hidden sm:block cursor-pointer" id="profile-button-text">
                <p class="text-xs font-bold text-gray-800 leading-none"><?php echo $_SESSION['username']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Administrator</p>
            </div>
            <button id="profile-button"
                class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all">
                <i class="fas fa-user-shield text-sm"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="profile-dropdown"
                class="absolute right-0 top-full mt-3 w-48 bg-white rounded-xl shadow-[0_10px_40px_-10px_rgba(0,0,0,0.15)] border border-gray-100 overflow-hidden hidden transition-all duration-200 opacity-0 transform scale-95 origin-top-right z-50">
                <div class="p-4 border-b border-gray-50 sm:hidden">
                    <p class="text-xs font-bold text-gray-800 leading-none"><?php echo $_SESSION['username']; ?></p>
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Administrator</p>
                </div>
                <div class="p-2">
                    <a href="#" onclick="document.getElementById('editAccountModal').classList.remove('hidden');"
                        class="flex items-center px-4 py-2.5 text-sm text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors group">
                        <i class="fas fa-user-edit w-5 group-hover:translate-x-1 transition-transform"></i>
                        <span class="font-medium">Edit Akun</span>
                    </a>
                </div>
                <div class="p-2">
                    <a href="#" id="logout-button"
                        class="flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors group">
                        <i class="fas fa-sign-out-alt w-5 group-hover:translate-x-1 transition-transform"></i>
                        <span class="font-medium">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Edit Account Modal -->
    <div id="editAccountModal"
        class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
            <h2 class="text-xl font-semibold mb-4">Edit Akun</h2>
            <form method="POST" action="../admin/edit_account.php" class="space-y-4">
                <input type="hidden" name="action" value="update_self">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="username">Username</label>
                    <input type="text" name="username" id="username" required
                        value="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="password">Password (leave blank to
                        keep current)</label>
                    <input type="password" name="password" id="password"
                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">Simpan</button>
                </div>
            </form>
            <button type="button" onclick="document.getElementById('editAccountModal').classList.add('hidden')"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <!-- Logout Confirmation Modal -->
    <div id="logoutConfirmModal"
        class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 relative">
            <h2 class="text-lg font-semibold mb-2">Konfirmasi Logout</h2>
            <p class="text-sm text-gray-600 mb-4">Apakah Anda yakin ingin logout?</p>
            <div class="flex justify-end space-x-2">
                <button id="logoutCancel" type="button"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Batal</button>
                <button id="logoutConfirm" type="button"
                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">Logout</button>
            </div>
            <button type="button" onclick="document.getElementById('logoutConfirmModal').classList.add('hidden')"
                class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <div class="container mx-auto px-4 md:px-8 py-6 md:py-8 flex-1">

        <script>
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mobileMenuBtn = document.getElementById('mobile-menu-button');
            const closeMenuBtn = document.getElementById('close-menu-button');
            const profileBtn = document.getElementById('profile-button');
            const profileBtnText = document.getElementById('profile-button-text');
            const profileDropdown = document.getElementById('profile-dropdown');

            function toggleMenu() {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }

            function toggleProfileDropdown(e) {
                e.stopPropagation();
                const isHidden = profileDropdown.classList.contains('hidden');

                if (isHidden) {
                    profileDropdown.classList.remove('hidden');
                    // Allow display block to apply before transitioning opacity/scale
                    setTimeout(() => {
                        profileDropdown.classList.remove('opacity-0', 'scale-95');
                        profileDropdown.classList.add('opacity-100', 'scale-100');
                    }, 10);
                } else {
                    closeProfileDropdown();
                }
            }

            function closeProfileDropdown() {
                profileDropdown.classList.remove('opacity-100', 'scale-100');
                profileDropdown.classList.add('opacity-0', 'scale-95');
                setTimeout(() => {
                    profileDropdown.classList.add('hidden');
                }, 200);
            }

            if (mobileMenuBtn) mobileMenuBtn.addEventListener('click', toggleMenu);
            if (closeMenuBtn) closeMenuBtn.addEventListener('click', toggleMenu);
            if (overlay) overlay.addEventListener('click', toggleMenu);
            if (profileBtn) profileBtn.addEventListener('click', toggleProfileDropdown);
            if (profileBtnText) profileBtnText.addEventListener('click', toggleProfileDropdown);

            // Close dropdowns when clicking outside
            document.addEventListener('click', function (e) {
                // Handle profile dropdown
                const profileContainer = document.getElementById('profile-menu-container');
                if (profileContainer && !profileContainer.contains(e.target)) {
                    if (!profileDropdown.classList.contains('hidden')) {
                        closeProfileDropdown();
                    }
                }
            });

            // Logout modal handlers
            const logoutBtn = document.getElementById('logout-button');
            const logoutModal = document.getElementById('logoutConfirmModal');
            const logoutConfirm = document.getElementById('logoutConfirm');
            const logoutCancel = document.getElementById('logoutCancel');

            if (logoutBtn) logoutBtn.addEventListener('click', function (e) {
                e.preventDefault();
                logoutModal.classList.remove('hidden');
            });

            if (logoutCancel) logoutCancel.addEventListener('click', function () {
                logoutModal.classList.add('hidden');
            });

            if (logoutConfirm) logoutConfirm.addEventListener('click', function () {
                window.location.href = '../auth/logout.php';
            });

            // Close modal when clicking outside modal content
            if (logoutModal) logoutModal.addEventListener('click', function (e) {
                if (e.target === logoutModal) {
                    logoutModal.classList.add('hidden');
                }
            });
        </script>