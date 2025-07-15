<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('vendor_id');
            $table->string('reference')->nullable();
            $table->decimal('total', 20, 2);
            $table->decimal('balance', 20, 2);
            $table->string('currency_code', 3)->default('MVR');
            $table->decimal('exchange_rate', 20, 6)->default(1);
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};