<?php

namespace App\Controllers;

use App\Core\Request;

class KaryawanDashboardController
{
    /**
     * Show the employee dashboard.
     */
    public function index(Request $request)
    {
        // Get the employee data that was attached to the request by the middleware
        $karyawan = $request->user();

        return "Halo {$karyawan->nama_karyawan}, selamat datang di dasbor karyawan!";
    }
}
