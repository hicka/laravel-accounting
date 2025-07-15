<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_credit_refunds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('credit_balance_id');
            $table->unsignedBigInteger('journal_entry_id')->nullable();
            $table->decimal('amount', 20, 2);
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 20, 6);
            $table->decimal('base_currency_amount', 20, 2);
            $table->string('refund_method')->default('cash'); // or 'bank'
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_credit_refunds');
    }
};