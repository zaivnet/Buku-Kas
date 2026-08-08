<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('outlet_id')->constrained('outlets');
            $table->date('date');
            $table->unsignedBigInteger('amount'); // Nominal Rupiah (integer)
            $table->string('payer_name', 150); // Atas Nama
            $table->text('description')->nullable();
            $table->string('proof_image_path', 255)->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index('date');
            $table->index('outlet_id');
            $table->index('category_id');
            $table->index('type');
            $table->index(['outlet_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
