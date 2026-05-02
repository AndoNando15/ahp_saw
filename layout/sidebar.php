<!-- Sidebar -->
<aside id="sidebar" class="w-full md:w-64 bg-indigo-900 text-white flex-shrink-0 shadow-xl fixed md:sticky md:top-0 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out h-full md:h-screen overflow-y-auto">
    <div class="p-6 flex items-center justify-between shrink-0">
        <h1 class="text-xl font-bold tracking-wider">AHP-SAW</h1>
        <button class="md:hidden text-white focus:outline-none" id="close-menu-button">
            <i class="fas fa-times text-xl"></i>
        </button>
    </div>
    
    <nav class="mt-2 px-4 pb-8 space-y-6 overflow-y-auto">
        <!-- Group: Utama -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Master Data</p>
            <div class="space-y-1">
                <a href="dashboard.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-tachometer-alt w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Dashboard</span>
                </a>
                <a href="criteria.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-list-check w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Data Kriteria</span>
                </a>
                <a href="alternatives.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-users w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Data Alternatif</span>
                </a>
            </div>
        </div>

        <!-- Group: AHP -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Metode AHP</p>
            <div class="space-y-1">
                <a href="respondents.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-users-cog w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Kuesioner Pakar</span>
                </a>
                <a href="comparisons.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-exchange-alt w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Matriks Agregasi</span>
                </a>
                <a href="ahp.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-calculator w-5 group-hover:text-indigo-300"></i>
                    <span class="ml-3 text-sm font-medium">Hasil Pembobotan</span>
                </a>
            </div>
        </div>

        <!-- Group: SAW -->
        <div>
            <p class="px-3 mb-2 text-[10px] font-black text-indigo-400 uppercase tracking-[0.2em]">Metode SAW</p>
            <div class="space-y-1">
                <a href="saw.php" class="flex items-center p-3 rounded-xl hover:bg-indigo-800 transition duration-200 group">
                    <i class="fas fa-trophy w-5 group-hover:text-indigo-300 text-yellow-400"></i>
                    <span class="ml-3 text-sm font-bold">Ranking Akhir</span>
                </a>
            </div>
        </div>

        <!-- Group: Sistem -->
        <div class="pt-4 border-t border-indigo-800/50">
            <a href="../auth/logout.php" class="flex items-center p-3 rounded-xl hover:bg-red-600/20 text-red-400 transition duration-200 group">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span class="ml-3 text-sm font-bold">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Overlay for mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 hidden md:hidden"></div>

<!-- Main Content Area -->
<main class="flex-1 min-h-screen bg-gray-50/50 flex flex-col">
    <!-- Topbar -->
    <header class="bg-white/80 backdrop-blur-md shadow-sm py-4 px-4 md:px-8 flex justify-between items-center sticky top-0 z-30 border-b border-gray-100 shrink-0">
        <div class="flex items-center">
            <button class="md:hidden mr-4 text-indigo-900 focus:outline-none bg-indigo-50 w-10 h-10 rounded-xl flex items-center justify-center" id="mobile-menu-button">
                <i class="fas fa-bars text-lg"></i>
            </button>
            <h2 class="text-sm md:text-base font-bold text-gray-700 tracking-tight">SPK AHP-SAW</h2>
        </div>
        <div class="flex items-center space-x-2 md:space-x-4">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-gray-800 leading-none"><?php echo $_SESSION['username']; ?></p>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-1">Administrator</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-white flex items-center justify-center shadow-lg shadow-indigo-100">
                <i class="fas fa-user-shield text-sm"></i>
            </div>
        </div>
    </header>
    
    <div class="container mx-auto px-4 md:px-8 py-6 md:py-8 flex-1">

<script>
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const mobileMenuBtn = document.getElementById('mobile-menu-button');
    const closeMenuBtn = document.getElementById('close-menu-button');

    function toggleMenu() {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    }

    mobileMenuBtn.addEventListener('click', toggleMenu);
    closeMenuBtn.addEventListener('click', toggleMenu);
    overlay.addEventListener('click', toggleMenu);
</script>
