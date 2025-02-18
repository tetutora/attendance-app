<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function admin_login()
    {
        return view('admin.login');
    }
}
