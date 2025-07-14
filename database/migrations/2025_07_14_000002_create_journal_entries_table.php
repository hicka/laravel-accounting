<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->uuid('reference')->unique(); // UUID for traceability
            $table->string('description')->nullable();
            $table->date('date');
            $table->string('currency_code');         // e.g., USD
            $table->decimal('exchange_rate', 18, 6); // exchange rate relative to base currency
            $table->decimal('base_currency_amount', 18, 2); // calculated value
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journal_entries');
    }
};