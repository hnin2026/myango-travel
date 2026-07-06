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
    Schema::rename(
        'available_dates',
        'travel_periods'
    );
}

public function down(): void
{
    Schema::rename(
        'travel_periods',
        'available_dates'
    );
}
};
