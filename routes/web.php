<?php

use App\Core\Route;
use App\Core\Response;

// ============================================================================
// PUBLIC ROUTES
// ============================================================================

Route::get('/', ['AuthController', 'loginForm']);
Route::get('/login', ['AuthController', 'loginForm']);
Route::get('/register', ['AuthController', 'registerForm']);
Route::post('/login', ['AuthController', 'login']);
Route::post('/register', ['AuthController', 'register']);
Route::get('/logout', ['AuthController', 'logout']);

// ============================================================================
// SEEDING ROUTE (HANYA UNTUK DEVELOPMENT)
// ============================================================================
// Jalankan URL ini sekali untuk mengisi database dengan akun contoh
Route::get('/buat-akun-demo', function() {
    // 1. Buat Akun Dosen
    \App\Models\User::create([
        'name' => 'Dosen Pengampu',
        'email' => 'dosen@unima.ac.id',
        'password' => password_hash('123', PASSWORD_BCRYPT),
        'role' => 'dosen',
        'nim_nip' => '199001012023011001'
    ]);

    // 2. Buat Akun Mahasiswa
    \App\Models\User::create([
        'name' => 'Mahasiswa Contoh',
        'email' => 'mhs@unima.ac.id',
        'password' => password_hash('123', PASSWORD_BCRYPT),
        'role' => 'mahasiswa',
        'nim_nip' => '22101105001'
    ]);

    // 3. Buat Mata Kuliah Contoh
    $dosen = \App\Models\User::where('email', 'dosen@unima.ac.id')->first();
    if ($dosen) {
        \App\Models\Course::create([
            'name' => 'Pemrograman Web Lanjut',
            'code' => 'TIF1234',
            'dosen_id' => $dosen->id
        ]);
        \App\Models\Course::create([
            'name' => 'Kecerdasan Buatan',
            'code' => 'TIF5678',
            'dosen_id' => $dosen->id
        ]);
    }

    echo "<h1>Sukses!</h1>";
    echo "<p>Akun demo dan mata kuliah berhasil dibuat.</p>";
    echo "<ul>
            <li><strong>Dosen:</strong> dosen@unima.ac.id / 123</li>
            <li><strong>Mahasiswa:</strong> mhs@unima.ac.id / 123</li>
          </ul>";
    echo "<a href='/login'>Kembali ke Login</a>";
});

// ============================================================================
// DOSEN ROUTES (Protected Middleware: 'dosen')
// ============================================================================

Route::group(['middleware' => 'dosen'], function($router) {
    // Dashboard
    $router->get('/dosen/dashboard', ['DosenController', 'dashboard']);
    
    // Manajemen Mata Kuliah
    $router->get('/dosen/course/{id}', ['DosenController', 'showCourse']);
    
    // Manajemen Sesi (Absensi)
    $router->post('/dosen/session/create', ['DosenController', 'createSession']);
    $router->get('/dosen/session/{id}', ['DosenController', 'showSession']);
    
    // Manajemen Tugas
    $router->post('/dosen/assignment/create', ['DosenController', 'createAssignment']);
});

// ============================================================================
// MAHASISWA ROUTES (Protected Middleware: 'mahasiswa')
// ============================================================================

Route::group(['middleware' => 'mahasiswa'], function($router) {
    // Dashboard (Kelas Saya)
    $router->get('/mahasiswa/dashboard', ['MahasiswaController', 'dashboard']);
    
    // Pendaftaran Mata Kuliah (Enrollment)
    $router->get('/mahasiswa/courses', ['MahasiswaController', 'indexCourses']); // Browse
    $router->post('/mahasiswa/enroll', ['MahasiswaController', 'enroll']);       // Action Enroll
    
    // Detail Kelas
    $router->get('/mahasiswa/course/{id}', ['MahasiswaController', 'showCourse']);
    
    // Absensi (Scan & Upload)
    // Menggunakan Closure routing untuk logika view sederhana
    $router->get('/mahasiswa/session/{id}/attend', function($id) {
        $session = \App\Models\ClassSession::find($id);
        if (!$session) return Response::redirect('/mahasiswa/dashboard');
        
        // Tampilkan view sesuai tipe sesi
        if ($session->type == 'offline') {
            return Response::view('mahasiswa.scan', ['session' => $session]);
        }
        return Response::view('mahasiswa.upload_attendance', ['session' => $session]);
    });
    
    // Proses Submit Absensi
    $router->post('/mahasiswa/attendance/submit', ['MahasiswaController', 'submitAttendance']);

    // Pengumpulan Tugas
    $router->get('/mahasiswa/assignment/{id}', ['MahasiswaController', 'showAssignment']);
    $router->post('/mahasiswa/assignment/submit', ['MahasiswaController', 'submitAssignment']);
});