<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journal_template_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('template_id');
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('account_id');
            $table->enum('type', ['debit', 'credit']);
            $table->decimal('amount', 20, 6);
            $table->json('meta')->nullable(); // optional metadata like GST, etc.
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journal_template_lines');
    }
};