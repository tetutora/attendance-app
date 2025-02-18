<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Requests\LoginRequest;

class AdminLoginController extends Controller
{
    public function admin_login()
    {
        return view('admin.login');
    }

    public function admin_authenticate(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->where('role', 'admin')->first();

        if ($user && Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            Auth::login($user);
            return redirect()->route('admin.attendance-list');
        }
        return redirect()->back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
