<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        $tableName = config('accounting.tables.payments', 'payments');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->date('date');
                $table->decimal('amount', 20, 6);
                $table->boolean('is_credit')->default(false);
                $table->string('currency_code', 3)->default('MVR');
                $table->decimal('exchange_rate', 20, 6)->default(1);
                $table->boolean('inverse')->default(false);
                $table->string('method')->nullable(); // cash, card, etc.
                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                foreach (['tenant_id', 'invoice_id', 'date', 'amount', 'method','currency_code','exchange_rate','inverse','customer_id','is_credit'] as $column) {
                    if (!Schema::hasColumn($table->getTable(), $column)) {
                        match ($column) {
                            'tenant_id', 'invoice_id', 'customer_id' => $table->unsignedBigInteger($column)->nullable(),
                            'date' => $table->date($column)->nullable(),
                            'amount' => $table->decimal($column, 20, 6)->nullable(),
                            'is_credit' => $table->boolean('is_credit')->default(false),
                            'method' => $table->string($column)->nullable(),
                            'currency_code' => $table->string('currency_code', 3)->default('MVR'),
                            'exchange_rate' => $table->decimal('exchange_rate', 20, 6)->default(1),
                            'inverse' => $table->boolean($column)->nullable(),
                            default => null
                        };
                    }
                }
            });
        }
    }
};