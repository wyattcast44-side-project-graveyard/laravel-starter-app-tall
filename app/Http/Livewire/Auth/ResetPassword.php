<?php

namespace App\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;

class ResetPassword extends Component
{
    public $email = '';

    public $new_password = '';

    public $new_password_confirmation = '';

    public $token = '';

    public function mount($token)
    {
        $this->email = request()->query('email', '');

        $this->token = $token;
    }

    public function resetPassword()
    {
        $this->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'new_password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::reset([
            'email' => $this->email,
            'password' => $this->new_password,
            'password_confirmation' => $this->new_password_confirmation,
            'token' => $this->token,
        ], function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password),
            ])->save();

            $user->setRememberToken(Str::random(60));

            event(new PasswordReset($user));

            Auth::login($user);
        });

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('dashboard');
        }

        $this->addError('email', trans($status));
    }

    public function render()
    {
        return view('auth.reset-password')
            ->extends('layouts.auth')
            ->section('content');
    }
}
