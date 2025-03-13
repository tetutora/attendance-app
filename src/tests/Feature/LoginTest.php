<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_email_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_password_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_invalid_login_credentials()
    {
        $this->withoutMiddleware();

        User::factory()->create([
            'email' => 'testtest@example.com',
            'password' => bcrypt('password123')
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);
    }
}
