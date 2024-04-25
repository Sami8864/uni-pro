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
        Schema::table('user_details', function (Blueprint $table) {
            $table->string('gender')->nullable();
            $table->string('age')->nullable();
            $table->string('ethnicity')->nullable();
            // $table->dropColumn('weight_height');
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_details', function (Blueprint $table) {
            $table->dropColumn('gender');
            $table->dropColumn('age');
            $table->dropColumn('ethnicity');
            $table->dropColumn('height');
            $table->dropColumn('weight');
        });
    }
};
