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
    if (Schema::hasTable('available_dates')) {
        Schema::rename(
            'available_dates',
            'travel_periods'
        );
    }
}

public function down(): void
{
    if (Schema::hasTable('travel_periods') && !Schema::hasTable('available_dates')) {
        Schema::rename(
            'travel_periods',
            'available_dates'
        );
    }
}
};
