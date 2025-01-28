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
        Schema::create('warranty_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Name of the warranty
            $table->text('description'); // Description of the warranty
            $table->string('locale')->index();
            $table->foreignId('warranty_id')->constrained('warranties')->onDelete('cascade');

            $table->unique(['warranty_id', 'locale']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warranty_translations');
    }
};
