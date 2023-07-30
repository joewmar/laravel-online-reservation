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
            $table->tinyText('room_id')->nullable();
            $table->integer('room_rate_id')->nullable();
            $table->integer('pax');
            $table->tinyText('accommodation_type');
            $table->tinyText('payment_method');
            $table->integer('age')->nullable();
            $table->string('menu')->nullable();;
            $table->date('check_in');
            $table->date('check_out');
            $table->tinyInteger('status')->default(0); /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => check-out */
            $table->string('additional_menu')->nullable();
            $table->string('amount')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('downpayment')->nullable();
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
