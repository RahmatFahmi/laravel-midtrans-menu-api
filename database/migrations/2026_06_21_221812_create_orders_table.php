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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('table_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // 'pending', 'proses', 'selesai', 'batal'
            $table->string('payment_method')->nullable(); // 'CASH', 'NON_CASH'
            $table->string('payment_status')->default('unpaid'); // 'unpaid', 'settlement', 'pending', 'expired'
            $table->unsignedInteger('total_price');
            $table->string('snap_token')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
