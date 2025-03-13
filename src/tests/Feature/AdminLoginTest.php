<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_email_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123'
        ]);
        $response->assertSessionHasErrors('email');
    }

    public function test_password_is_required()
    {
        $this->withoutMiddleware();

        $response = $this->post('/admin/login', [
            'email' => 'test@example.com',
            'password' => ''
        ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_invalid_login_credentials()
    {
        $this->withoutMiddleware();

        $response = $this->post(route('admin.authenticate'), [
            'email' => 'wrong@example.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

    }
}
