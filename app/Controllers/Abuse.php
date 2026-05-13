<?php

namespace App\Controllers;

class Abuse extends BaseController
{
    public function home(): string
    {
        return view('pages/home.php');
    }

    public function dashboard(): string
    {
        return view('admin/dashboard.php');
    }
}
