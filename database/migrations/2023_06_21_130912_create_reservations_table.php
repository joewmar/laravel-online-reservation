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
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('room_id')->nullable();
            $table->integer('room_rate_id')->nullable();
            $table->integer('pax');
            $table->tinyText('accommodation_type');
            $table->tinyText('payment_method');
            $table->integer('age')->nullable();
            $table->json('menu')->nullable();;
            $table->date('check_in');
            $table->date('check_out');
            $table->tinyInteger('status')->default(0); /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
            $table->json('additional_menu')->nullable();
            $table->json('amount')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('downpayment')->default(0);
            $table->text('request_message')->nullable();
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
