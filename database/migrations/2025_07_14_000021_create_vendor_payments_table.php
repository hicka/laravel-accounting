<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('bill_id')->nullable(); // support applying to a specific bill
            $table->decimal('amount', 20, 2);
            $table->string('currency_code', 3)->default('MVR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};