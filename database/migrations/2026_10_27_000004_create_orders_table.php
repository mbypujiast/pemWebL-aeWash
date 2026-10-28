<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 30)->unique();

            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            // Staff yang menginput order
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', ['diterima', 'dicuci', 'disetrika', 'siap_ambil'])
                  ->default('diterima');
            $table->enum('payment_status', ['belum_bayar', 'lunas'])
                  ->default('belum_bayar');

            $table->decimal('total_weight', 8, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            $table->date('order_date');
            $table->date('estimated_finish_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
