<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id');
            $table->foreignId('category_id');
            $table->string('name');
            $table->decimal('purchase_cost', 20, 2);
            $table->date('purchase_date');
            $table->decimal('residual_value', 20, 2)->default(0);
            $table->date('start_depreciation_date');
            $table->foreignId('asset_account_id')->nullable();
            $table->json('meta')->nullable(); // MIRA: green tax, disposal info, etc.
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('fixed_assets');
    }
};