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
            $table->json('roomid')->nullable();
            $table->tinyInteger('roomrateid')->nullable();
            $table->tinyInteger('pax');
            $table->tinyInteger('tour_pax')->nullable();
            $table->tinyText('accommodation_type');
            $table->tinyText('payment_method');
            $table->integer('age')->nullable();
            // $table->json('menu')->nullable();;
            $table->date('check_in');
            $table->date('check_out');
            $table->tinyInteger('status')->default(0); /* 0 => pending, 1 => confirmed, 2 => check-in, 3 => done, 4 => canceled, 5 => disaprove, 6 => reshedule*/
            // $table->json('additional')->nullable();
            $table->json('amount')->nullable();
            $table->decimal('total')->nullable();
            $table->decimal('downpayment')->nullable();
            $table->text('message')->nullable();
            $table->string('valid_id');
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
