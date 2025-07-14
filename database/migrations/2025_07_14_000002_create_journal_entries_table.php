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
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journal_entries');
    }
};