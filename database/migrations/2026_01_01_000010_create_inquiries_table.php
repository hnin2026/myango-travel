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
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tour_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('customer_name');
            $table->string('nationality')->nullable();

            $table->string('phone')->nullable();
            $table->string('email');

            $table->integer('number_of_adults')->default(1);
            $table->integer('number_of_children')->default(0);

            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();

            $table->text('message')->nullable();

            $table->string('status')->default('new');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
