<?php
// ============================================================
// database/factories/UserFactory.php
// ============================================================
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake('id_ID')->name(),
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => 'guru',
            'is_active'         => true,
            'last_login_at'     => null,
            'remember_token'    => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn() => ['is_active' => false]);
    }

    public function role(string $role): static
    {
        return $this->state(fn() => ['role' => $role]);
    }

    public function mudir(): static
    {
        return $this->role('mudir');
    }
    public function guru(): static
    {
        return $this->role('guru');
    }
    public function admin(): static
    {
        return $this->role('admin');
    }
    public function kesantrian(): static
    {
        return $this->role('kesantrian');
    }
    public function sysadmin(): static
    {
        return $this->role('sysadmin');
    }
    public function wakilKurikulum(): static
    {
        return $this->role('wakil_kurikulum');
    }
}
