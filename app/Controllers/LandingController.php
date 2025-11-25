<?php

namespace App\Controllers;

use App\Core\Response;

class LandingController
{
    public function index()
    {
        // Data yang dikirim ke view
        $data = [
            'framework' => 'LayVX',
            'version' => '1.0.0',
            'phpVersion' => phpversion(),
        ];

        return Response::view('welcome', $data);
    }
}