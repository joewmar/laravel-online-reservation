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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('room_lists', 'id')->onDelete('cascade');
            $table->integer('room_no');
            $table->boolean('availability')->default(false);
            $table->json('customer')->nullable(); // Ex. customerID-pax ({'reservationID' : 'pax'})
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
