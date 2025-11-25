<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;

class LandingController {

    public function index() {
       
        return Response::view('index');
    }
}