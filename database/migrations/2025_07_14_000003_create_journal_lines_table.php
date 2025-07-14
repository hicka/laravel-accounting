<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 20, 6); // in base currency
            $table->json('meta')->nullable();
            $table->decimal('base_currency_amount', 20, 6)->nullable();
            $table->enum('type', ['debit', 'credit']);
            $table->text('memo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journal_lines');
    }
};