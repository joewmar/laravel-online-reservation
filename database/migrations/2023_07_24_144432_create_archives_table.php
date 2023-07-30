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
        Schema::create('archives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name')->nullable();
            $table->integer('age')->nullable();
            $table->string('country')->nullable();
            $table->string('nationality')->nullable();
            $table->string('contact')->nullable();
            $table->string('email')->nullable();
            $table->integer('user_id')->nullable();
            $table->tinyText('room_id')->nullable();
            $table->integer('pax');
            $table->tinyText('accommodation_type');
            $table->tinyText('payment_method');
            $table->string('menu')->nullable();
            $table->date('check_in');
            $table->date('check_out');
            $table->tinyInteger('status')->default(0); /* 0 => done, 1 => disaprove, 2 => cancellation, 3 => ? */
            $table->string('additional_menu')->nullable();
            $table->string('amount')->nullable();
            $table->decimal('total')->nullable();
            $table->string('message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
