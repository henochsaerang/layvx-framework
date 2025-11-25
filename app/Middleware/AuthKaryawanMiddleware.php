<?php
namespace App\Middleware;

use App\Core\Middleware;
use App\Core\Request;
use App\Core\Response;
use App\Models\Karyawan; // Menggunakan model Karyawan
use App\Core\Session;
use Closure;

class AuthKaryawanMiddleware implements Middleware 
{
    public function handle(Request $request, Closure $next) 
    {
        $karyawanId = Session::get('karyawan_id'); // Menggunakan session key yang berbeda

        if (!$karyawanId) {
            return Response::redirect('/login-karyawan'); // Redirect ke login karyawan
        }
        
        $karyawan = Karyawan::find($karyawanId);

        if (!$karyawan) {
            // Jika tidak ada ID atau Karyawan tidak ditemukan di DB
            Session::forget('karyawan_id');
            return Response::redirect('/login-karyawan'); // Redirect ke login karyawan
        }

        $request->setUser($karyawan); // "Titipkan" data karyawan ke request
        return $next($request);
    }
}
