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
        Schema::create('online_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reservation_id');
            $table->foreign('reservation_id')->references('id')->on('reservations')->onDelete('cascade');
            $table->string('payment_method'); /* 0=>gcash, 1 => paypal */
            $table->string('payment_name'); /* 0=>gcash, 1 => paypal */
            $table->string('image');
            $table->decimal('amount', 8, 2);
            $table->boolean('approval')->nullable(); /* 1 => aproval, 0 => disaproval, 3 => partial approve */
            // $table->tinyInteger('attempt')->default(0); /* 2 attepmt was max to give your chance */
            $table->string('reference_no');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
