<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password as PasswordRule;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-superadmin {--email=} {--name=} {--password= : Leave empty to auto-generate a strong random password}';
    protected $description = 'Create (or promote) the first superadmin account for a new environment';

    public function handle(): int
    {
        $email = $this->option('email') ?: $this->ask('Email superadmin');
        $name  = $this->option('name') ?: $this->ask('Nama');
        $password = $this->option('password') ?: Str::password(20);

        try {
            validator(compact('email', 'password'), [
                'email'    => ['required', 'email'],
                'password' => ['required', PasswordRule::min(12)],
            ])->validate();
        } catch (ValidationException $e) {
            $this->error(implode(' ', $e->validator->errors()->all()));
            return self::FAILURE;
        }

        $user = User::firstOrNew(['email' => $email]);
        $isNew = ! $user->exists;
        $user->name = $name ?: ($user->name ?: 'Superadmin');
        $user->password = Hash::make($password);
        $user->save();
        $user->syncRoles(['superadmin']);

        $this->newLine();
        $this->info($isNew ? 'Akun superadmin dibuat.' : 'Akun ditemukan dan dipromosikan/direset menjadi superadmin.');
        $this->line("Email    : {$email}");
        if (! $this->option('password')) {
            $this->warn("Password : {$password}");
            $this->warn('Catat password ini sekarang — tidak akan ditampilkan lagi. Segera login dan ganti.');
        }

        return self::SUCCESS;
    }
}
