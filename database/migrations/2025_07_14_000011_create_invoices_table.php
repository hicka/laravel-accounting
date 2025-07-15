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
                $table->date('due_date')->nullable();
                $table->string('invoice_number')->unique();
                $table->decimal('total', 20, 6);
                $table->decimal('paid_amount', 20, 6)->default(0);
                $table->decimal('balance', 20, 6)->default(0);
                $table->string('currency_code', 3)->default('MVR');
                $table->string('status')->default('unpaid');
                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                foreach ([
                             'tenant_id', 'customer_id', 'date', 'invoice_number', 'total', 'balance', 'status','currency_code','due_date','paid_amount'
                         ] as $column) {
                    if (!Schema::hasColumn($table->getTable(), $column)) {
                        match ($column) {
                            'tenant_id', 'customer_id' => $table->unsignedBigInteger($column)->nullable(),
                            'date' => $table->date($column)->nullable(),
                            'invoice_number' => $table->string($column)->nullable(),
                            'total', 'balance' => $table->decimal($column, 20, 6)->nullable(),
                            'status' => $table->string($column)->nullable(),
                            'currency_code' => $table->string('currency_code', 3)->default('MVR'),
                            'due_date' =>  $table->date('due_date')->nullable(),
                            'paid_amount' => $table->decimal('paid_amount', 20, 6)->nullable(),
                            default => null
                        };
                    }
                }
            });
        }
    }
};