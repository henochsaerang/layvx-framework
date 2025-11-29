<?php

namespace App\Commands;

use App\Core\Command;

class MakePwaCommand extends Command
{
    protected $signature = 'buat:pwa';
    protected $description = 'Mengkonfigurasi aplikasi agar bisa diinstal di Android (PWA Support).';

    public function handle(array $args = [])
    {
        $rootDir = dirname(__DIR__, 2);
        $publicDir = $rootDir . '/public';
        $viewsDir = $rootDir . '/views';

        // 1. Cek direktori public
        if (!is_dir($publicDir)) {
            echo "Error: Folder 'public' tidak ditemukan." . "\n";
            return 1;
        }

        // 2. Generate manifest.json
        echo "Membuat public/manifest.json..." . "\n";
        $manifestContent = $this->getManifestContent();
        file_put_contents($publicDir . '/manifest.json', $manifestContent);

        // 3. Generate service-worker.js
        echo "Membuat public/service-worker.js..." . "\n";
        $swContent = $this->getServiceWorkerContent();
        file_put_contents($publicDir . '/service-worker.js', $swContent);
        
        // 4. Generate offline.html
        if (!is_dir($viewsDir)) {
            mkdir($viewsDir, 0755, true);
        }
        echo "Membuat views/offline.html..." . "\n";
        $offlinePageContent = $this->getOfflinePageContent();
        file_put_contents($viewsDir . '/offline.html', $offlinePageContent);
        
        // 5. Output instruksi
        echo "\n\033[32mPWA berhasil dikonfigurasi!\033[0m\n";
        echo "Tambahkan kode berikut di dalam tag <head> pada file layout utama Anda (misal: views/layouts/app.php):\n\n";
        echo "--------------------------------------------------------------------------------\n";
        echo '<link rel="manifest" href="/manifest.json">' . "\n";
        echo "<script> if ('serviceWorker' in navigator) { navigator.serviceWorker.register('/service-worker.js'); } </script>" . "\n";
        echo "--------------------------------------------------------------------------------\n";

        return 0;
    }

    private function getManifestContent(): string
    {
        $manifest = [
            'name' => 'LayVX App',
            'short_name' => 'LayVX',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => '#3b82f6',
            'icons' => [
                [
                    'src' => '/assets/img/icons/icon-192x192.png',
                    'type' => 'image/png',
                    'sizes' => '192x192',
                ],
                [
                    'src' => '/assets/img/icons/icon-512x512.png',
                    'type' => 'image/png',
                    'sizes' => '512x512',
                ],
            ],
        ];

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private function getServiceWorkerContent(): string
    {
        return <<<JS
const CACHE_NAME = 'layvx-pwa-cache-v1';
const OFFLINE_URL = 'offline.html';
const ASSETS_TO_CACHE = [
    '/',
    '/offline.html',
    '/assets/css/style.css', // Ganti dengan path CSS utama Anda
    '/assets/js/app.js'      // Ganti dengan path JS utama Anda
];

// Event: Install
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('Service Worker: Caching assets');
            return cache.addAll(ASSETS_TO_CACHE);
        })
    );
});

// Event: Activate
// Membersihkan cache lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_NAME) {
                        console.log('Service Worker: Clearing old cache');
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Event: Fetch
// Menyajikan konten dari cache jika offline
self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith(
            (async () => {
                try {
                    const preloadResponse = await event.preloadResponse;
                    if (preloadResponse) {
                        return preloadResponse;
                    }

                    const networkResponse = await fetch(event.request);
                    return networkResponse;
                } catch (error) {
                    console.log('Service Worker: Fetch failed; returning offline page.');
                    const cache = await caches.open(CACHE_NAME);
                    const cachedResponse = await cache.match(OFFLINE_URL);
                    return cachedResponse;
                }
            })()
        );
    } else {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            })
        );
    }
});
JS;
    }

    private function getOfflinePageContent(): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f3f4f6;
            color: #374151;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .container {
            max-width: 400px;
        }
        h1 {
            font-size: 2rem;
            color: #1f2937;
        }
        p {
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Anda Sedang Offline</h1>
        <p>Maaf, koneksi internet Anda terputus. Halaman ini tidak dapat dimuat.</p>
        <p>Silakan periksa koneksi Anda dan coba lagi.</p>
    </div>
</body>
</html>
HTML;
    }
}
