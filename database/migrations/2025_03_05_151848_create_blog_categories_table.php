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
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('title_kh');
            $table->integer('order_index')->default(1);
            $table->string('image')->nullable();
            $table->string('parent_code')->nullable(); // Allow null for root categories
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::table('blog_categories', function (Blueprint $table) {
            $table->foreign('parent_code')
                ->references('code')
                ->on('blog_categories')
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
         Schema::table('blog_categories', function (Blueprint $table) {
            $table->dropForeign(['parent_code']);
        });

        Schema::dropIfExists('blog_categories');
    }
};
