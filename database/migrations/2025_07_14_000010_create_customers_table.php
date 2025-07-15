<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        $tableName = config('accounting.tables.customers', 'customers');

        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('address')->nullable();
                $table->timestamps();
            });
        } else {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'tenant_id')) {
                    $table->unsignedBigInteger('tenant_id')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'name')) {
                    $table->string('name')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'email')) {
                    $table->string('email')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'phone')) {
                    $table->string('phone')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'address')) {
                    $table->string('address')->nullable();
                }
            });
        }
    }
};