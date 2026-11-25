<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ContentSeeder::class);

        // Panel access. Set ADMIN_EMAIL / ADMIN_PASSWORD in .env, or a random
        // password is generated and printed once.
        $email = env('ADMIN_EMAIL', 'hola@fernandovasquez.tech');
        if (! User::query()->where('email', $email)->exists()) {
            $password = env('ADMIN_PASSWORD') ?: Str::password(20, symbols: false);
            User::query()->create(['name' => 'Fernando Vásquez', 'email' => $email, 'password' => $password]);
            $this->command?->info("Panel user: {$email} / ".(env('ADMIN_PASSWORD') ? '(ADMIN_PASSWORD)' : $password));
        }
    }
}
