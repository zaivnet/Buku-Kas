<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use InvalidArgumentException;

class TransactionService
{
    public function __construct(
        protected ImageUploadService $imageUploadService
    ) {}

    /**
     * Catat transaksi baru (Income / Expense).
     *
     * @param array $data
     * @param User $actor
     * @param UploadedFile|null $proofFile
     * @return Transaction
     */
    public function create(array $data, User $actor, ?UploadedFile $proofFile = null): Transaction
    {
        // 1. Outlet Scoping: Staff dipaksa ke outlet miliknya sendiri (04-RULES.md §2.5)
        if ($actor->isStaff()) {
            $data['outlet_id'] = $actor->outlet_id;
        }

        // 2. Validasi sinkronisasi tipe transaksi & tipe kategori
        $category = Category::findOrFail($data['category_id']);
        $typeValue = is_object($data['type']) ? $data['type']->value : $data['type'];
        $categoryTypeValue = is_object($category->type) ? $category->type->value : $category->type;

        if ($typeValue !== $categoryTypeValue) {
            throw new InvalidArgumentException("Tipe transaksi ('{$typeValue}') tidak sesuai dengan tipe kategori ('{$categoryTypeValue}').");
        }

        // 3. Upload & compress bukti transaksi jika ada
        if ($proofFile) {
            $data['proof_image_path'] = $this->imageUploadService->store($proofFile);
        }

        $data['created_by'] = $actor->id;

        return Transaction::create($data);
    }

    /**
     * Perbarui data transaksi.
     *
     * @param Transaction $transaction
     * @param array $data
     * @param User $actor
     * @param UploadedFile|null $proofFile
     * @param bool $removeProof
     * @return Transaction
     */
    public function update(Transaction $transaction, array $data, User $actor, ?UploadedFile $proofFile = null, bool $removeProof = false): Transaction
    {
        // 1. Outlet Scoping untuk Staff
        if ($actor->isStaff()) {
            $data['outlet_id'] = $actor->outlet_id;
        }

        // 2. Validasi sinkronisasi tipe transaksi & tipe kategori
        $category = Category::findOrFail($data['category_id']);
        $typeValue = is_object($data['type']) ? $data['type']->value : $data['type'];
        $categoryTypeValue = is_object($category->type) ? $category->type->value : $category->type;

        if ($typeValue !== $categoryTypeValue) {
            throw new InvalidArgumentException("Tipe transaksi ('{$typeValue}') tidak sesuai dengan tipe kategori ('{$categoryTypeValue}').");
        }

        // 3. Handle perubahan gambar bukti
        if ($removeProof || $proofFile) {
            if ($transaction->proof_image_path) {
                $this->imageUploadService->delete($transaction->proof_image_path);
            }
            $data['proof_image_path'] = null;
        }

        if ($proofFile) {
            $data['proof_image_path'] = $this->imageUploadService->store($proofFile);
        }

        $data['updated_by'] = $actor->id;

        $transaction->update($data);

        return $transaction;
    }

    /**
     * Hapus (soft delete) transaksi.
     *
     * @param Transaction $transaction
     * @param User $actor
     * @return void
     */
    public function delete(Transaction $transaction, User $actor): void
    {
        $transaction->delete();
    }
}
