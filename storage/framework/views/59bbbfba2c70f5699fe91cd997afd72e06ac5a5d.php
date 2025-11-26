<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS UNIMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans flex flex-col min-h-screen">

    <!-- Top Navbar -->
    <nav class="bg-blue-900 text-white shadow-lg sticky top-0 z-50 h-16 flex items-center justify-between px-4 lg:px-6">
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="lg:hidden text-white focus:outline-none hover:text-blue-200 transition">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <a href="/" class="font-bold text-xl tracking-wider flex items-center gap-2">
                <i class="fas fa-graduation-cap"></i> <span class="hidden sm:inline">LMS UNIMA</span>
            </a>
        </div>

        <?php if (App\Core\Session::has('user_id')) { ?>
        <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
                <div class="text-sm font-semibold"><?php echo htmlspecialchars(App\Core\Session::get('name'), ENT_QUOTES, 'UTF-8'); ?></div>
                <div class="text-xs text-blue-300 uppercase"><?php echo htmlspecialchars(App\Core\Session::get('role'), ENT_QUOTES, 'UTF-8'); ?></div>
            </div>
            <a href="/logout" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded text-sm transition shadow-sm" title="Logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
        <?php } else { ?>
        <a href="/login" class="text-white hover:text-blue-200 font-semibold text-sm">Login</a>
        <?php } ?>
    </nav>

    <!-- Main Layout Wrapper -->
    <div class="flex flex-1 relative">
        
        <?php if (App\Core\Session::has('user_id')) { ?>
        <!-- SIDEBAR -->
        <aside id="sidebar" class="bg-white border-r border-gray-200 w-64 flex-shrink-0 
            fixed inset-y-0 left-0 z-40 
            transform -translate-x-full transition-transform duration-300 ease-in-out
            lg:translate-x-0 lg:sticky lg:top-16 lg:h-[calc(100vh-4rem)] lg:z-0
            overflow-y-auto sidebar-scroll">
            
            <div class="p-4 space-y-6">
                <!-- Menu Dosen -->
                <?php if (App\Core\Session::get('role') == 'dosen') { ?>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-3">Menu Dosen</div>
                    <nav class="space-y-1">
                        <a href="/dosen/dashboard" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors <?php echo htmlspecialchars(strpos($_SERVER['REQUEST_URI'], '/dosen/dashboard') !== false ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600', ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-tachometer-alt w-5 text-center"></i> Dashboard
                        </a>
                        <!-- Link Jadwal Mengajar diarahkan ke Dashboard karena daftar kelas ada di sana -->
                        <a href="/dosen/dashboard" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                            <i class="fas fa-chalkboard-teacher w-5 text-center"></i> Kelas Ajar
                        </a>
                        <!-- Placeholder link untuk fitur masa depan -->
                        <a href="#" onclick="alert('Fitur Rekap Nilai akan segera hadir!')" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                            <i class="fas fa-chart-bar w-5 text-center"></i> Rekap Nilai
                        </a>
                    </nav>
                </div>
                <?php } ?>

                <!-- Menu Mahasiswa -->
                <?php if (App\Core\Session::get('role') == 'mahasiswa') { ?>
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-3">Menu Mahasiswa</div>
                    <nav class="space-y-1">
                        <a href="/mahasiswa/dashboard" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors <?php echo htmlspecialchars(strpos($_SERVER['REQUEST_URI'], '/mahasiswa/dashboard') !== false ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600', ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-book-reader w-5 text-center"></i> Kelas Saya
                        </a>
                        <a href="/mahasiswa/courses" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors <?php echo htmlspecialchars(strpos($_SERVER['REQUEST_URI'], '/mahasiswa/courses') !== false ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100 hover:text-blue-600', ENT_QUOTES, 'UTF-8'); ?>">
                            <i class="fas fa-search w-5 text-center"></i> Cari Mata Kuliah
                        </a>
                        <!-- Link Absensi diarahkan ke Dashboard karena absen dilakukan per kelas -->
                        <a href="/mahasiswa/dashboard" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                            <i class="fas fa-qrcode w-5 text-center"></i> Scan Absensi
                        </a>
                    </nav>
                </div>
                <?php } ?>

                <!-- Pengaturan Umum -->
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3 px-3">Akun</div>
                    <nav class="space-y-1">
                         <a href="#" onclick="alert('Halaman Profil pengguna.')" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-md transition-colors text-gray-700 hover:bg-gray-100 hover:text-blue-600">
                            <i class="fas fa-user-circle w-5 text-center"></i> Profil Saya
                        </a>
                         <a href="/logout" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 transition-colors">
                            <i class="fas fa-sign-out-alt w-5 text-center"></i> Keluar
                        </a>
                    </nav>
                </div>
            </div>
        </aside>

        <!-- Overlay for Mobile -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden lg:hidden glass-effect" onclick="toggleSidebar()"></div>
        <?php } ?>

        <!-- MAIN CONTENT -->
        <main class="flex-1 min-w-0 bg-gray-50 p-4 lg:p-8 overflow-x-hidden">
            
<div class="flex justify-center items-center py-8">
    <div class="w-full max-w-lg bg-white p-8 rounded-lg shadow-lg border border-gray-100">
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-blue-900">Buat Akun Baru</h1>
            <p class="text-gray-500 text-sm mt-1">LMS Universitas Negeri Manado</p>
        </div>
        
        <!-- Pesan Error -->
        <?php if (isset($error)) { ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-4 text-sm" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        <?php } ?>

        <form action="/register" method="POST" class="space-y-4">
            <?php tuama_field(); ?>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                <input type="text" id="name" name="name" required class="w-full border border-gray-300 rounded px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required class="w-full border border-gray-300 rounded px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" id="password" name="password" required class="w-full border border-gray-300 rounded px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>

            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Daftar sebagai</label>
                <select id="role" name="role" required class="w-full border border-gray-300 rounded px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm bg-white">
                    <option value="mahasiswa">Mahasiswa</option>
                    <option value="dosen">Dosen</option>
                </select>
            </div>

            <div>
                <label for="nim_nip" class="block text-sm font-medium text-gray-700 mb-1">NIM / NIP</label>
                <input type="text" id="nim_nip" name="nim_nip" required class="w-full border border-gray-300 rounded px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 transition text-sm">
            </div>

            <button type="submit" class="w-full bg-blue-700 hover:bg-blue-800 text-white font-bold py-2.5 rounded transition shadow-md">
                Daftar
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <span class="text-gray-600">Sudah punya akun?</span>
            <a href="/login" class="text-blue-600 hover:text-blue-800 font-semibold hover:underline">Login di sini</a>
        </div>
    </div>
</div>

        </main>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 text-center py-4 text-xs text-gray-500">
        &copy; 2025 LMS Universitas Negeri Manado.
    </footer>

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');

        function toggleSidebar() {
            if (sidebar && overlay) {
                const isClosed = sidebar.classList.contains('-translate-x-full');
                if (isClosed) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }
        }

        if (btn) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                toggleSidebar();
            });
        }
    </script>
</body>
</html>