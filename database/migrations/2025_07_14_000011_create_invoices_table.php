<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        $tableName = config('accounting.tables.invoices', 'invoices');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('customer_id');
                $table->date('date');
                $table->string('invoice_number')->unique();
                $table->decimal('total', 20, 6);
                $table->decimal('balance_due', 20, 6)->default(0);
                $table->string('status')->default('unpaid');
                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                foreach ([
                             'tenant_id', 'customer_id', 'date', 'invoice_number', 'total', 'balance_due', 'status'
                         ] as $column) {
                    if (!Schema::hasColumn($table->getTable(), $column)) {
                        match ($column) {
                            'tenant_id', 'customer_id' => $table->unsignedBigInteger($column)->nullable(),
                            'date' => $table->date($column)->nullable(),
                            'invoice_number' => $table->string($column)->nullable(),
                            'total', 'balance_due' => $table->decimal($column, 20, 6)->nullable(),
                            'status' => $table->string($column)->nullable(),
                            default => null
                        };
                    }
                }
            });
        }
    }
};