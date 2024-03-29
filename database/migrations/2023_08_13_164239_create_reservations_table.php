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
        Schema::create('reservations', function (Blueprint $table) {
            $table->string('id')->unique()->primary(); // Define the custom_id field as the primary key.
            $table->foreignId('user_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->foreignId('offline_user_id')->nullable()->constrained('user_offlines', 'id')->onDelete('set null');
            $table->json('roomid')->nullable();
            $table->tinyInteger('roomrateid')->nullable();
            $table->tinyInteger('pax');
            $table->tinyInteger('tour_pax')->nullable();
            $table->tinyText('accommodation_type');
            $table->tinyText('payment_method');
            $table->date('check_in');
            $table->date('check_out');
            $table->tinyInteger('status')->default(0); /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => done/check-out, 4 => reshedule, 5 => cancel, 6 => disaprove*, 7 => pending reschedule, 8 => pending cancel */
            $table->json('transaction')->nullable();
            $table->json('message')->nullable();
            $table->json('otherinfo')->nullable(); // incase the user account was deleted 
            $table->dateTime('payment_cutoff')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
