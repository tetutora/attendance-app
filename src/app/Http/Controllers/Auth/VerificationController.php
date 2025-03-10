<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\VerifyEmail;

class VerificationController extends Controller
{
    /**
     * Handle the email verification process.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request)
    {
        $user = \App\Models\User::findOrFail($request->id);

        if (hash_equals((string) $request->hash, sha1($user->email))) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect('auth.login')->with('verified', true);
    }

    /**
     * Show the email verification notice.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('auth.verify-email');
    }

    public function showAttendance()
{
    $attendance = Auth::user()->attendance()->latest()->first(); // ユーザーの最新の勤怠情報を取得
    $status = Auth::user()->status; // ユーザーのステータスを取得

    return view('general.attendance', compact('attendance', 'status'));
}

    /**
     * Resend the email verification link.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        if ($user && !$user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return back()->with('resent', true);
    }
}
