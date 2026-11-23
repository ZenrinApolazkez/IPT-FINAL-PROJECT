<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('laundry:admin')]
#[Description('Create a laundry system administrator account')]
class CreateLaundryAdmin extends Command
{
    public function handle(): int
    {
        $name = $this->ask('Administrator name');
        $username = $this->ask('Username');
        $email = $this->ask('Email address');
        $password = $this->secret('Password (minimum 8 characters)');
        $passwordConfirmation = $this->secret('Confirm password');

        $validator = validator([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'min:3', 'max:32', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $user = new User($validator->validated());
        $user->role = 'admin';
        $user->save();

        $this->info('Administrator account created. Sign in at /login.');

        return self::SUCCESS;
    }
}
