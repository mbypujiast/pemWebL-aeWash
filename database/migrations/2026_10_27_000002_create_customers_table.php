<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            // Opsional: dipakai jika customer punya akun login
            $table->foreignId('user_id')->nullable()->unique()
                  ->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('phone', 20);
            $table->text('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
