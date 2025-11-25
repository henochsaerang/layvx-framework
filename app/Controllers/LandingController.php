<?php

namespace App\Controllers;

use App\Models\Food; // Import Model Food

class LandingController {

    public function index($request) {
       
        render('index');
    }
}