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
        Schema::create('played_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trick_id')->constrained('tricks');
            $table->foreignId('player_id')->constrained('game_players');
            $table->string('card');
            $table->integer('play_order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('played_cards');
    }
};
