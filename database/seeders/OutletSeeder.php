<?php

namespace Database\Seeders;

use App\Models\Outlet;
use Illuminate\Database\Seeder;

class OutletSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $outlets = [
            [
                'name'      => 'Outlet 1',
                'address'   => 'Jl. Solo Km 10, Yogyakarta',
                'is_active' => true,
            ],
            [
                'name'      => 'Outlet 2',
                'address'   => 'Jl. Kaliurang Km 5, Yogyakarta',
                'is_active' => true,
            ],
        ];

        foreach ($outlets as $outletData) {
            Outlet::updateOrCreate(
                ['name' => $outletData['name']],
                $outletData
            );
        }
    }
}
