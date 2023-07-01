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
        Schema::create('tour_menu_lists', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->text('inclusion');
            $table->integer('no_day');
            $table->decimal('hrs', 2, 1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_menu_lists');
    }
};
