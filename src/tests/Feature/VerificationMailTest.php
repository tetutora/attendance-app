<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Notifications\SendEmailVerificationNotification;
use Tests\TestCase;
use Carbon\Carbon;

class VerificationMailTest extends TestCase
{
    use RefreshDatabase;

    // 会員登録後、認証メールが送信されるか
    public function test_verification_email_is_sent_after_registration()
    {
        Mail::fake();
        $this->withoutMiddleware();

        $userData = [
            'name' => 'Test User',
            'email' => 'testmail@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post(route('register'), $userData);

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');

        $this->assertDatabaseHas('users', [
            'email' => 'testmail@example.com',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect('/email/verify');
    }

    // メール認証画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移するか
    public function test_email_verification_screen_and_redirect()
    {
        $response = $this->get(route('verification.notice'));

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから');

        $response->assertSee('<a href="https://mailtrap.io"', false);
    }

    // 会員登録後、メール認証を完了すると勤怠登録ページに遷移するか
    public function test_email_verification_redirects_to_attendance_page()
    {
        $this->withoutMiddleware();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->email_verified_at = Carbon::now();
        $user->save();

        $this->actingAs($user);

        $verificationUrl = route('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->email),
        ]);

        $response = $this->get($verificationUrl);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);

        $response->assertStatus(302);

        $response->assertRedirect(route('general.attendance'));
    }
}
