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
        Schema::create('labour_day_workings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('labour_id');
            $table->tinyInteger('day_number')->comment('Day number corresponding to the day of the week');
            $table->enum('day_name', ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'])->comment('Day of the week');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('break_minute')->default(0)->comment('Break time in minutes'); 
            $table->string('working_hour')->default(0);
            $table->float('per_hour_amount')->default(0)->comment('Amount paid per hour');
            $table->float('day_amount')->default(0);
            $table->timestamps();
            $table->foreign('labour_id')->references('id')->on('labours')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labour_day_workings');
    }
};
