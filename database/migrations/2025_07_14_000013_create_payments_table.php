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
                $table->date('date');
                $table->decimal('amount', 20, 6);
                $table->string('method')->nullable(); // cash, card, etc.
                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                foreach (['tenant_id', 'invoice_id', 'date', 'amount', 'method'] as $column) {
                    if (!Schema::hasColumn($table->getTable(), $column)) {
                        match ($column) {
                            'tenant_id', 'invoice_id' => $table->unsignedBigInteger($column)->nullable(),
                            'date' => $table->date($column)->nullable(),
                            'amount' => $table->decimal($column, 20, 6)->nullable(),
                            'method' => $table->string($column)->nullable(),
                            default => null
                        };
                    }
                }
            });
        }
    }
};