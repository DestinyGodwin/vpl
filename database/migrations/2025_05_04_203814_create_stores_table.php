<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('university_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['regular', 'food']);
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['is_active', 'is_inactive'])->default('is_active');
            $table->timestamp('next_payment_due')->nullable();
            $table->timestamp('created_at')->nullable();
             $table->timestamp('updated_at')->nullable();
            $table->softDeletes();
      
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
