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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_kh');
            $table->string('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->integer('order_index')->default(1);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();

            $table->string('blog_category_code')->nullable();
            $table->foreign('blog_category_code')
                ->references('code')
                ->on('blog_categories')
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
        Schema::dropIfExists('blogs');
    }
};
