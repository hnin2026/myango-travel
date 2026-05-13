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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_id')->constrained()->onDelete('cascade');
            $table->foreignId('hotel_id')->constrained()->onDelete('cascade');
            $table->foreignId('available_date_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone');
            $table->integer('num_persons');
            $table->decimal('base_price', 10, 2);
            $table->decimal('hotel_upgrade_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'paid', 'cancelled'])->default('pending');
            $table->string('ref_code')->unique();
            $table->string('cancellation_token')->unique()->nullable();
            $table->date('payment_deadline')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
