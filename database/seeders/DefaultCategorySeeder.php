<?php

namespace Database\Seeders;

use App\Enums\TransactionTypeEnum;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DefaultCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Kategori Pemasukan (Income)
        $incomeCategories = [
            'Setoran Penjualan Outlet',
            'Dipinjam',
            'Pemasukan Lainnya',
        ];

        foreach ($incomeCategories as $name) {
            Category::updateOrCreate(
                ['name' => $name, 'type' => TransactionTypeEnum::INCOME->value],
                ['is_active' => true]
            );
        }

        // Kategori Pengeluaran (Expense)
        $expenseCategories = [
            'Belanja Stok',
            'Gaji Karyawan',
            'Pinjaman Karyawan',
            'Pengeluaran Lainnya',
        ];

        foreach ($expenseCategories as $name) {
            Category::updateOrCreate(
                ['name' => $name, 'type' => TransactionTypeEnum::EXPENSE->value],
                ['is_active' => true]
            );
        }
    }
}
