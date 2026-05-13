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
    Schema::create('hotel_season_prices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
        $table->enum('season', ['low', 'normal', 'peak']);
        $table->decimal('upgrade_price', 10, 2)->default(0);
        $table->timestamps();
        $table->unique(['hotel_id', 'season']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_season_prices');
    }
};
