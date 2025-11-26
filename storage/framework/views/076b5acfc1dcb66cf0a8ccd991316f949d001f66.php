<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS UNIMA - LayVX</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <nav class="bg-blue-900 text-white shadow-md">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <a href="/" class="font-bold text-xl tracking-wide">LMS UNIMA</a>
            
            <?php if (App\Core\Session::has('user_id')) { ?>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-300">
                        Halo, <?php echo htmlspecialchars(App\Core\Session::get('name'), ENT_QUOTES, 'UTF-8'); ?> (<?php echo htmlspecialchars(ucfirst(App\Core\Session::get('role')), ENT_QUOTES, 'UTF-8'); ?>)
                    </span>
                    <a href="/logout" class="bg-red-600 hover:bg-red-700 px-3 py-1 rounded text-sm transition">Logout</a>
                </div>
            <?php } ?>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8 min-h-screen">
        
<h1 class="text-2xl font-bold mb-6">Kelas Saya</h1>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <?php foreach ($courses as $course): ?>
    <a href="/mahasiswa/course/<?php echo htmlspecialchars($course->id, ENT_QUOTES, 'UTF-8'); ?>" class="group block bg-white border border-l-4 border-l-blue-500 rounded shadow hover:shadow-lg transition p-6">
        <div class="flex justify-between items-start">
            <div>
                <h3 class="text-xl font-bold text-gray-800 group-hover:text-blue-600 transition"><?php echo htmlspecialchars($course->name, ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="text-sm text-gray-500 mt-1">Dosen: <?php echo htmlspecialchars($course->dosen->name ?? 'Tim Dosen', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <span class="text-gray-300">&rarr;</span>
        </div>
    </a>
    <?php endforeach; ?>
    
    <?php if (empty($courses)) { ?>
        <div class="col-span-2 bg-yellow-50 p-4 rounded text-yellow-800 border border-yellow-200">
            Anda belum terdaftar di kelas manapun.
        </div>
    <?php } ?>
</div>

    </main>

    <footer class="bg-gray-200 text-center py-4 text-sm text-gray-600 mt-8">
        &copy; 2025 LMS UNIMA - Built with LayVX Framework
    </footer>

</body>
</html>