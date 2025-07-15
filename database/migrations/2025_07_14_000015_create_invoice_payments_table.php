<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('payment_id');
            $table->decimal('amount', 20, 2);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('invoice_payments');
    }
};