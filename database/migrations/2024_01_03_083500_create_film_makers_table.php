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

        Schema::create('unions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('film_makers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onUpdate('cascade')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->string('compnay_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('bio')->nullable();
            $table->string('imdb_link')->nullable();
            $table->string('actoraccess_link')->nullable();
            $table->string('casting_link')->nullable();
            $table->foreignId('union_id')->nullable()->constrained('unions')->onUpdate('cascade')->onDelete('cascade');;
            $table->timestamps();
        });
        Schema::create('filmmaker_saved_feeds', function (Blueprint $table) {
            $table->id();
            $table->string('feed_id');
            $table->foreignId('filmmaker_id')->constrained('film_makers')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('film_makers');
        Schema::dropIfExists('filmmaker_saved_feeds');
    }
};
