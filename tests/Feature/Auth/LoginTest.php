<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Livewire\Livewire;
use App\Http\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_login_page()
    {
        $this->get(route('login'))->assertOk();
    }

    public function test_livewire_component_is_present()
    {
        $this->get(route('login'))->assertSeeLivewire('auth.login');
    }

    public function test_is_redirected_if_logged_in()
    {
        $this->signIn();

        Livewire::test(Login::class)
            ->assertRedirect(route('dashboard'));
    }

    public function test_email_is_required()
    {
        Livewire::test(Login::class)
            ->set('email', '')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => [
                'required'
            ]]);
    }

    public function test_password_is_required()
    {
        $user = $this->signIn();

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', '')
            ->call('authenticate')
            ->assertHasErrors(['password' => [
                'required'
            ]]);
    }

    public function test_email_must_be_valid()
    {
        Livewire::test(Login::class)
            ->set('email', 'not-an-email')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => [
                'email'
            ]]);
    }

    public function test_email_must_exist()
    {
        Livewire::test(Login::class)
            ->set('email', 'not-a-registered-email@email.com')
            ->set('password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['email' => [
                'exists'
            ]]);
    }

    public function test_valid_credentials_can_authenticate()
    {
        $user = User::factory()->create([
            'email' => 'user@email.com',
            'password' => 'password',
        ]);

        Livewire::test(Login::class)
            ->set('email', $user->email)
            ->set('password', 'password')
            ->call('authenticate')
            ->assertRedirect(route('dashboard'));
    }
}