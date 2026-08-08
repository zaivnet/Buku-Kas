<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email    = env('ADMIN_EMAIL', 'admin@bukukas.local');
        $password = env('ADMIN_PASSWORD', 'password123');

        if (!env('ADMIN_EMAIL') || !env('ADMIN_PASSWORD')) {
            $this->command->warn('ADMIN_EMAIL / ADMIN_PASSWORD belum diatur di .env. Menggunakan kredensial default:');
            $this->command->info("   Email    : {$email}");
            $this->command->info("   Password : {$password}");
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'      => 'Administrator',
                'password'  => $password,
                'role'      => RoleEnum::ADMIN,
                'outlet_id' => null,
                'is_active' => true,
            ]
        );

        // Buat 1 sampel user Staff untuk Outlet 1 (untuk kemudahan testing)
        $outlet1 = Outlet::where('name', 'Outlet 1')->first();
        if ($outlet1) {
            User::updateOrCreate(
                ['email' => 'staff1@bukukas.local'],
                [
                    'name'      => 'Staff Outlet 1',
                    'password'  => 'password123',
                    'role'      => RoleEnum::STAFF,
                    'outlet_id' => $outlet1->id,
                    'is_active' => true,
                ]
            );
        }
    }
}
