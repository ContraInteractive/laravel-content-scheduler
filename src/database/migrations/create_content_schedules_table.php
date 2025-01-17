<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship fields
            $table->morphs('schedulable'); // Creates 'schedulable_id' and 'schedulable_type'

            // Scheduling fields
            $table->timestamp('scheduled_at');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('unpublished_at')->nullable();

            $table->enum('status', ['scheduled', 'published', 'unpublished', 'canceled'])
                ->default('scheduled');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for better performance
            $table->index(['schedulable_id', 'schedulable_type']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
}