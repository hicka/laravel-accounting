<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('asset_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->string('name');
            $table->integer('useful_life_years');
            $table->decimal('residual_percentage', 5, 2)->default(0);
            $table->enum('method', ['straight_line', 'reducing_balance'])->default('straight_line');
            $table->foreignId('asset_account_id');
            $table->foreignId('accum_depreciation_account_id');
            $table->foreignId('depreciation_expense_account_id');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('asset_categories');
    }
};