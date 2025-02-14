<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;


class RegisterController extends Controller
{
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $userData = $request->all();

        $userData['password'] = bcrypt($userData['password']);

        $user = User::create($userData);

        Auth::login($user);

        $attendance = Attendance::where('user_id', $user->id)->latest()->first();

        return view('general.attendance', compact('attendance'));
    }

    public function attendance()
    {
        return view('general.attendance');
    }
}
