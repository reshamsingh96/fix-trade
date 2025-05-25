
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
        Schema::create('labour_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->longText('image_url')->nullable();
            $table->string('image_public_id')->nullable();
            $table->uuid('labour_id');
            $table->foreign('labour_id')->references('id')->on('labours')->onUpdate('cascade')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_images');
    }
};
