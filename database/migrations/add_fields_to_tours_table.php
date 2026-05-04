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
    Schema::table('tours', function (Blueprint $table) {
        $table->string('title_mm')->nullable()->after('title');
        $table->longText('description_en')->nullable()->after('description');
        $table->longText('description_mm')->nullable()->after('description_en');
        $table->longText('additional_info_en')->nullable()->after('description_mm');
        $table->longText('additional_info_mm')->nullable()->after('additional_info_en');
        $table->dropColumn('description');
    });
}

public function down(): void
{
    Schema::table('tours', function (Blueprint $table) {
        $table->dropColumn([
            'title_mm',
            'description_en', 
            'description_mm',
            'additional_info_en',
            'additional_info_mm'
        ]);
        $table->text('description')->nullable();
    });
}
};
