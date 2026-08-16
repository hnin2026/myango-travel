<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('reference')->nullable()->unique()->after('id');
        });

        // Populate references for existing inquiries
        $inquiries = DB::table('inquiries')->orderBy('id')->get();
        foreach ($inquiries as $inquiry) {
            DB::table('inquiries')
                ->where('id', $inquiry->id)
                ->update([
                    'reference' => 'INQ-' . str_pad($inquiry->id, 4, '0', STR_PAD_LEFT)
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn('reference');
        });
    }
};
