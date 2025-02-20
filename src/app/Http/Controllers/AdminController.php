<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;


class AdminController extends Controller
{
    public function index(Request $request)
{
    $selectedDate = Carbon::parse($request->get('date', now()->format('Y-m-d')));

    $attendances = Attendance::whereDate('clock_in', $selectedDate)->get();

    return view('admin.attendance-list', compact('attendances', 'selectedDate'));
}

}
