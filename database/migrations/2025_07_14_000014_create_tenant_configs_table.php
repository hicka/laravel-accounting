<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('tenant_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();

            // Default account references
            $table->unsignedBigInteger('default_receivable_account_id')->nullable();
            $table->unsignedBigInteger('default_payable_account_id')->nullable();
            $table->unsignedBigInteger('default_cash_account_id')->nullable();
            $table->unsignedBigInteger('default_sales_account_id')->nullable();
            $table->unsignedBigInteger('default_expense_account_id')->nullable();
            $table->unsignedBigInteger('default_credit_account_id')->nullable();

            $table->json('meta')->nullable(); // for any future expansion
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('tenant_configs');
    }
};