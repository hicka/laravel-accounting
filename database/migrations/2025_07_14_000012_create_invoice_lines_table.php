<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        $tableName = config('accounting.tables.invoice_lines', 'invoice_lines');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('invoice_id');
                $table->unsignedBigInteger('account_id');
                $table->string('description')->nullable();
                $table->decimal('amount', 20, 6);

                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                foreach (['invoice_id', 'account_id', 'description', 'amount'] as $column) {
                    if (!Schema::hasColumn($table->getTable(), $column)) {
                        match ($column) {
                            'invoice_id', 'account_id' => $table->unsignedBigInteger($column)->nullable(),
                            'description' => $table->string($column)->nullable(),
                            'amount' => $table->decimal($column, 20, 6)->nullable(),
                            default => null
                        };
                    }
                }
            });
        }
    }
};