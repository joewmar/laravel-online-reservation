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
        Schema::create('web_contents', function (Blueprint $table) {
            $table->id();
            $table->json('hero')->nullable();
            $table->json('gallery')->nullable();
            $table->json('tour')->nullable();
            $table->json('contact')->nullable();
            $table->json('payment')->nullable();
            $table->boolean('operation')->default(true);
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_contents');
    }
};
