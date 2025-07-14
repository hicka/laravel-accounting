<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tableName = config('accounting.tenant_table', 'tenants');

        if (!Schema::hasTable($tableName)) {
            // Create full tenants table
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('region_module')->default('global');
                $table->string('base_currency')->default('MVR');
                $table->timestamps();
            });
        } else {
            // Just add columns if they don't exist
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'region_module')) {
                    $table->string('region_module')->default('global');
                }

                if (!Schema::hasColumn($tableName, 'base_currency')) {
                    $table->string('base_currency')->default('MVR');
                }
            });
        }
    }

    public function down(): void
    {
        $tableName = config('accounting.tenant_table', 'tenants');

        if (Schema::hasTable($tableName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'region_module')) {
                    $table->dropColumn('region_module');
                }

                if (Schema::hasColumn($tableName, 'base_currency')) {
                    $table->dropColumn('base_currency');
                }
            });
        }
    }
};