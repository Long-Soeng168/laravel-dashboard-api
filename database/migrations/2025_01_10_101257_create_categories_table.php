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
        // Step 1: Create categories table (without foreign key)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('title_kh');
            $table->integer('order_index')->default(1);
            $table->string('image')->nullable();
            $table->string('parent_code')->nullable(); // Allow null for root categories
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Step 2: Add foreign key constraint after table creation
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_code')
                ->references('code')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key before dropping the table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_code']);
        });

        // Drop the categories table
        Schema::dropIfExists('categories');
    }
};
