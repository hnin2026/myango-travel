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
        Schema::create('tours', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('title_mm')->nullable();
            $table->longText('description_en')->nullable();
            $table->longText('description_mm')->nullable();
            $table->longText('additional_info_en')->nullable();
            $table->longText('additional_info_mm')->nullable();
            $table->integer('duration_days');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->string('location');
            $table->string('thumbnail')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};
