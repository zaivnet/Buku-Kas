<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy
{
    /**
     * Cek apakah transaksi sudah dikunci untuk diedit/dihapus oleh Staff (karena lewat X hari, default 7 hari).
     */
    public function isLockedForStaff(Transaction $transaction): bool
    {
        $lockDays = 7; // Batas hari edit staff (MVP default 7 hari)
        $transactionDate = $transaction->date ?? $transaction->created_at;

        return $transactionDate->diffInDays(now()) > $lockDays;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Admin, Staff, Viewer bisa lihat list transaksi
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        if ($user->isAdmin() || $user->isViewer()) {
            return true;
        }

        if ($user->isStaff()) {
            return $user->outlet_id === $transaction->outlet_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Viewer tidak boleh buat transaksi
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            // Staff hanya boleh edit transaksi outlet miliknya dan yang BELUM dikunci (<= 7 hari)
            return $user->outlet_id === $transaction->outlet_id && !$this->isLockedForStaff($transaction);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            // Staff hanya boleh hapus transaksi outlet miliknya dan yang BELUM dikunci (<= 7 hari)
            return $user->outlet_id === $transaction->outlet_id && !$this->isLockedForStaff($transaction);
        }

        return false;
    }
}
