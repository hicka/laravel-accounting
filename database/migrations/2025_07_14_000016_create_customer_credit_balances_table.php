<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_credit_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->decimal('amount', 20, 2)->default(0); // in original currency
            $table->string('currency_code', 3)->default('MVR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->decimal('base_currency_amount', 20, 2)->default(0); // for unified logic
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_credit_balances');
    }
};