<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['role' => UserRole::CUSTOMER]);

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create(['is_active' => false]);

        $this->post(route('login.post'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_customer_can_register_with_minimum_password_length(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Customer Baru',
            'email' => 'baru@example.test',
            'phone' => '08123456789',
            'address' => 'Jalan Demo Nomor 1',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('customer.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'baru@example.test', 'role' => 'customer']);
        $this->assertDatabaseHas('customer_addresses', ['address' => 'Jalan Demo Nomor 1']);
    }

    public function test_forgot_password_sends_reset_notification_without_disclosing_account_existence(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_google_callback_creates_and_authenticates_customer(): void
    {
        $googleUser = (new SocialiteUser)->map([
            'id' => 'google-123',
            'name' => 'Google Customer',
            'email' => 'google@example.test',
        ]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('customer.dashboard'));

        $this->assertAuthenticated();
        $user = User::where('email', 'google@example.test')->firstOrFail();
        $this->assertSame(UserRole::CUSTOMER, $user->role);
        $this->assertTrue(Hash::check('not-the-random-password', $user->password) === false);
    }
}
