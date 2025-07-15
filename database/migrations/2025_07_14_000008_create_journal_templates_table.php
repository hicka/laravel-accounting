<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('journal_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('currency_code', 3)->default('MVR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->boolean('inverse')->default(false);
            $table->date('start_date')->nullable(); // when it begins
            $table->string('recurrence')->nullable(); // daily, weekly, monthly, etc.
            $table->boolean('auto_post')->default(false); // whether to auto-post
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('journal_templates');
    }
};