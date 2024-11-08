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
        Schema::table('ninths', function (Blueprint $table) {
            $table->text('by_signal')->after('title'); // Agrega el campo after title
            $table->text('contrition')->after('by_signal'); // Agrega el campo después de by_signal
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ninths', function (Blueprint $table) {
            $table->dropColumn(['by_signal', 'contrition']);
        });
    }
};
