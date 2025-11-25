<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LayVX Framework</title>
    <style>
        :root {
            --bg-body: #f8fafc; 
            --text-main: #1e293b; 
            --text-muted: #64748b;
            --card-bg: #ffffff;
            
           
            --accent: #3b82f6; 
            --accent-hover: #2563eb; 
            --accent-glow: rgba(59, 130, 246, 0.15); 
            --accent-light-bg: rgba(59, 130, 246, 0.08); 
            
            --border: #e2e8f0;
            --code-bg: #0f172a; 
            --code-text: #93c5fd; 
            --warning-bg: #fff7ed;
            --warning-text: #9a3412;
            --warning-border: #fdba74;
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg-body: #0f172a; 
                --text-main: #f1f5f9;
                --text-muted: #94a3b8;
                --card-bg: #1e293b;
                --border: #334155;
                --warning-bg: #451a03;
                --warning-text: #fdba74;
                --warning-border: #9a3412;
            }
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            
            background-image: radial-gradient(circle at 50% 0%, var(--accent-glow) 0%, transparent 60%);
            background-repeat: no-repeat;
        }

        .top-corner {
            position: absolute;
            top: 2rem;
            right: 2rem;
        }

        .container {
            width: 100%;
            max-width: 900px;
            padding: 2rem 1rem;
            display: flex;
            flex-direction: column;
            margin-top: 3rem;
        }

        
        .brand-wrapper {
            text-align: center;
            margin-bottom: 3.5rem;
            position: relative;
        }

        .logo {
            font-size: 4rem;
            font-weight: 800;
            color: var(--accent);
            text-decoration: none;
            letter-spacing: -0.05em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        
        .logo-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px; 
            margin-bottom: 1.5rem;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1); 
        }

        .logo span { color: var(--text-main); }
        
        .hero-text {
            color: var(--text-muted);
            font-size: 1.125rem;
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
            margin-bottom: 2rem;
        }

        
        .card-stack {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            flex-grow: 1; 
        }

        .card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        
        .card-header {
            padding: 1.5rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }

        .card-title-group {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .card-icon {
            color: var(--accent);
            background: var(--accent-light-bg);
            padding: 0.6rem;
            border-radius: 0.5rem;
            display: flex;
        }

        .card-text h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .card-text p {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .chevron {
            transition: transform 0.3s ease;
            color: var(--text-muted);
        }

       
        .card-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s cubic-bezier(0, 1, 0, 1);
            background-color: var(--card-bg);
            border-top: 1px solid transparent;
        }

        .card-content-inner { padding: 0 1.5rem 1.5rem 1.5rem; }

       
        .card.active {
            border-color: var(--accent);
            box-shadow: 0 0 0 1px var(--accent);
        }

        .card.active .chevron {
            transform: rotate(180deg);
            color: var(--accent);
        }

        .card.active .card-body { border-top-color: var(--border); }

        
        h4 { margin: 1.5rem 0 0.5rem; font-size: 1rem; color: var(--text-main); font-weight: 700; }
        .doc-text { margin-bottom: 1rem; font-size: 0.95rem; color: var(--text-muted); }
        pre {
            background: var(--code-bg);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1rem;
            border: 1px solid var(--border);
        }
        code { font-family: 'Fira Code', 'Consolas', monospace; font-size: 0.85rem; color: var(--code-text); }
        
        .badge-ver {
            background: var(--accent);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: bold;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
        }

        
        .info-box {
            background-color: var(--warning-bg);
            border-left: 4px solid var(--warning-border);
            padding: 1rem;
            border-radius: 0.25rem;
            margin-bottom: 1.5rem;
            color: var(--warning-text);
            font-size: 0.9rem;
        }
        
        .file-path {
            display: inline-block;
            margin-top: 0.5rem;
            background: rgba(0,0,0,0.05);
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 600;
        }

        
        .footer {
            margin-top: 5rem;
            text-align: center;
            border-top: 1px solid var(--border);
            padding-top: 2rem;
            padding-bottom: 2rem;
            width: 100%;
        }

        .footer-tech {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .footer-author {
            font-size: 1rem;
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

       
        .heart-icon {
            color: #ef4444; 
            animation: beat 1.5s infinite;
        }

        @keyframes beat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.2); }
        }

    </style>
</head>
<body>

    <div class="top-corner">
        <span class="badge-ver">v<?= $version ?></span>
    </div>

    <div class="container">
        
        <div class="brand-wrapper">
            <div class="logo-icon">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--accent);"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
            </div>
            
            <a href="#" class="logo">Lay<span>VX</span></a>
            
            <div class="hero-text">
                Arsitektur Modern & Ringan.<br>
                Klik modul di bawah untuk memulai.
            </div>
        </div>

        <div class="card-stack">

            <div class="card" onclick="toggleCard(this)">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <div class="card-text">
                            <h3>Dokumentasi</h3>
                            <p>Panduan Instalasi & Referensi Lokal.</p>
                        </div>
                    </div>
                    <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="card-body">
                    <div class="card-content-inner">
                        
                        <div class="info-box">
                            <strong>Catatan Penting:</strong> Dokumentasi detail belum tersedia di internet.
                            <br>Silakan baca dokumentasi lengkap projek ini di file lokal:
                            <br>
                            <span class="file-path">tutorial/framework_explanation.md</span>
                        </div>

                        <h4>Konfigurasi Cepat (.env)</h4>
                        <p class="doc-text">Simpan konfigurasi database dan environment di file <code>.env</code>.</p>
<pre><code>APP_ENV=development
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=layvx_db</code></pre>

                        <h4>Routing Dasar</h4>
                        <p class="doc-text">Rute didefinisikan di <code>routes/web.php</code>.</p>
<pre><code>use App\Core\Route;

Route::get('/', ['LandingController', 'index']);</code></pre>
                    </div>
                </div>
            </div>

            <div class="card" onclick="toggleCard(this)">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 17 10 11 4 5"></polyline><line x1="12" y1="19" x2="20" y2="19"></line></svg>
                        </div>
                        <div class="card-text">
                            <h3>LayVX CLI</h3>
                            <p>Perintah terminal untuk otomatisasi tugas.</p>
                        </div>
                    </div>
                    <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="card-body">
                    <div class="card-content-inner">
                        <h4>Menjalankan Server</h4>
                        <pre><code>php layvx serve</code></pre>
                        
                        <h4>Generator Kode</h4>
                        <p class="doc-text">Membuat controller dan model beserta migrasinya secara instan.</p>
<pre><code>php layvx buat:controller UserController
php layvx buat:model Product -t</code></pre>
                        
                        <h4>Migrasi Database</h4>
                        <pre><code>php layvx migrasi</code></pre>
                    </div>
                </div>
            </div>

            <div class="card" onclick="toggleCard(this)">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                        </div>
                        <div class="card-text">
                            <h3>Service Container</h3>
                            <p>Auto-Wiring & Dependency Injection.</p>
                        </div>
                    </div>
                    <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="card-body">
                    <div class="card-content-inner">
                        <h4>Auto-Wiring dengan Reflection</h4>
                        <p class="doc-text">Cukup berikan <em>Type-Hint</em> pada konstruktor Anda. Container akan menyelesaikannya secara otomatis.</p>
<pre><code>class UserController {
    protected $request;

    // Otomatis diinjeksi tanpa konfigurasi manual
    public function __construct(App\Core\Request $request) {
        $this->request = $request;
    }
}</code></pre>
                    </div>
                </div>
            </div>

            <div class="card" onclick="toggleCard(this)">
                <div class="card-header">
                    <div class="card-title-group">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <div class="card-text">
                            <h3>Keamanan</h3>
                            <p>CSRF, XSS, dan Manajemen Sesi.</p>
                        </div>
                    </div>
                    <svg class="chevron" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </div>
                <div class="card-body">
                    <div class="card-content-inner">
                        <h4>Proteksi CSRF</h4>
                        <p class="doc-text">Form HTML otomatis dilindungi. Gunakan helper ini di dalam tag <code>&lt;form&gt;</code>:</p>
                        <pre><code>&lt;?= tuama_field() ?&gt;</code></pre>
                        
                        <h4>Manajemen Sesi Aman</h4>
                        <p class="doc-text">Hindari penggunaan <code>$_SESSION</code> langsung.</p>
<pre><code>use App\Core\Session;

Session::set('user_id', 123);
Session::regenerateToken(); // Mencegah Session Fixation</code></pre>
                    </div>
                </div>
            </div>

        </div> <div class="footer">
            <div class="footer-tech">
                LayVX Framework v<?= $version ?> — PHP v<?= $phpVersion ?>
            </div>
            <div class="footer-author">
                Terima kasih sudah menginstall LayVX dari <strong>Henoch Saerang</strong> 
                <svg class="heart-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
            </div>
        </div>
    </div>

    <script>
        function toggleCard(cardElement) {
            const body = cardElement.querySelector('.card-body');
            const isActive = cardElement.classList.contains('active');

            document.querySelectorAll('.card').forEach(c => {
                c.classList.remove('active');
                c.querySelector('.card-body').style.maxHeight = null;
            });

            if (!isActive) {
                cardElement.classList.add('active');
                body.style.maxHeight = body.scrollHeight + "px";
            }
        }
    </script>
</body>
</html>