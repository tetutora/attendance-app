<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminController extends Controller
{
    public function index()
    {
        $attendances = Attendance::all();

        return view('admin.attendance-list', compact('attendances'));
    }
}
