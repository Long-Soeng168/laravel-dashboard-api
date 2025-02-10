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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('short_description', 500);
            $table->text('description');
            $table->decimal('price', 10, 2);

            $table->string('category_code')->nullable();
            $table->foreign('category_code')
                ->references('code')
                ->on('categories')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Step 1: Drop foreign key first
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_code']); // Drop the foreign key constraint
        });

        // Step 2: Now it's safe to drop the table
        Schema::dropIfExists('products');
    }
};
