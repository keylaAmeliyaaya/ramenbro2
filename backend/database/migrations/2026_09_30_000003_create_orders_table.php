<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone', 30)->nullable();
            $table->text('customer_note')->nullable();
            $table->unsignedBigInteger('total')->default(0);
            $table->string('payment_method', 30)->default('cashier');
            $table->string('payment_status', 30)->default('pending');
            $table->string('order_status', 30)->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
