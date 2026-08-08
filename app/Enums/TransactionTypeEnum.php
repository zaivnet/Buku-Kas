<?php

namespace App\Enums;

enum TransactionTypeEnum: string
{
    case INCOME  = 'income';
    case EXPENSE = 'expense';

    public function label(): string
    {
        return match($this) {
            self::INCOME  => 'Pemasukan',
            self::EXPENSE => 'Pengeluaran',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::INCOME  => 'success',
            self::EXPENSE => 'danger',
        };
    }
}
