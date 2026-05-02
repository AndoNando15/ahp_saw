<!-- Sidebar -->
<aside class="w-full md:w-64 bg-indigo-900 text-white flex-shrink-0 shadow-xl">
    <div class="p-6 flex items-center justify-between">
        <h1 class="text-xl font-bold tracking-wider">AHP-SAW</h1>
        <button class="md:hidden text-white focus:outline-none" id="mobile-menu-button">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    <nav class="mt-4 px-4 space-y-2">
        <a href="dashboard.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-tachometer-alt w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Dashboard</span>
        </a>
        <a href="criteria.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-list-check w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Kriteria</span>
        </a>
        <a href="alternatives.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-users w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Alternatif</span>
        </a>
        <a href="respondents.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-users-cog w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Manajemen Responden</span>
        </a>
        <a href="comparisons.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-exchange-alt w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Perbandingan</span>
        </a>
        <a href="ahp.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-calculator w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Hasil AHP</span>
        </a>
        <a href="saw.php" class="flex items-center p-3 rounded-lg hover:bg-indigo-800 transition duration-200 group">
            <i class="fas fa-trophy w-6 group-hover:text-indigo-300"></i>
            <span class="ml-3">Ranking SAW</span>
        </a>
        <div class="pt-4 mt-4 border-t border-indigo-800">
            <a href="../auth/logout.php" class="flex items-center p-3 rounded-lg hover:bg-red-600 transition duration-200 group">
                <i class="fas fa-sign-out-alt w-6 group-hover:text-red-200"></i>
                <span class="ml-3">Logout</span>
            </a>
        </div>
    </nav>
</aside>

<!-- Main Content Area -->
<main class="flex-1 overflow-x-hidden overflow-y-auto">
    <!-- Topbar -->
    <header class="bg-white shadow-sm py-4 px-8 flex justify-between items-center">
        <h2 class="text-lg font-medium text-gray-700">Panel Administrator</h2>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Selamat Datang, <?php echo $_SESSION['username']; ?></span>
            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </header>
    
    <div class="container mx-auto px-8 py-8">
