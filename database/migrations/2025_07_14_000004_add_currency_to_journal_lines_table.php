<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->string('currency_code')->nullable()->after('amount');
            $table->decimal('exchange_rate', 20, 6)->nullable()->after('currency_code');
            $table->boolean('inverse')->default(false)->after('exchange_rate');
        });
    }

    public function down(): void {
        Schema::table('journal_lines', function (Blueprint $table) {
            $table->dropColumn(['currency_code', 'exchange_rate', 'inverse']);
        });
    }
};