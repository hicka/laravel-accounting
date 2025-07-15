<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('journal_entry_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('journal_entry_id');
            $table->unsignedBigInteger('user_id')->nullable(); // null for system actions
            $table->string('action'); // created, submitted, approved, rejected, updated, deleted
            $table->json('changes')->nullable(); // optional: before/after
            $table->timestamps();

            $table->foreign('journal_entry_id')->references('id')->on('journal_entries')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_audits');
    }
};