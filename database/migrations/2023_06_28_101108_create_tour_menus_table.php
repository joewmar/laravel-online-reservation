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
        Schema::create('tour_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('tour_menu_lists')->onDelete('cascade');
            $table->string('type');
            $table->decimal('price', 8, 2);
            $table->integer('pax');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_menus');
    }
};
