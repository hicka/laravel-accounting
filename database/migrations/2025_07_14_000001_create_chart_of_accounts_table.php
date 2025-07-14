<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique(); // e.g., 1000
            $table->string('name'); // e.g., Cash
            $table->enum('type', [
                'asset', 'liability', 'equity', 'revenue', 'expense','cost_of_sales','non_taxable','gst_input','gst_output'
            ]);
            $table->string('tax_type')->nullable()->after('type');
            $table->boolean('is_contra')->default(false); // for contra accounts
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('chart_of_accounts');
    }
};