<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();

            // Polymorphic relationship fields
            $table->morphs('schedulable');
            // This creates 'schedulable_id' and 'schedulable_type' columns

            // Scheduling fields
            $table->timestamp('publish_scheduled_at')->nullable();    // The intended/predicted time to publish
            $table->timestamp('unpublish_scheduled_at')->nullable();  // The intended/predicted time to unpublish

            // Actual event timestamps
            $table->timestamp('published_at')->nullable();   // The exact time we actually published
            $table->timestamp('unpublished_at')->nullable(); // The exact time we actually unpublished

            // Status: scheduled, published, unpublished, canceled
            $table->enum('status', ['scheduled', 'published', 'unpublished', 'canceled'])
                ->default('scheduled');

            $table->text('notes')->nullable();

            $table->timestamps(); // created_at, updated_at

            // Indexes
            $table->index(['schedulable_id', 'schedulable_type']);
            $table->index('publish_scheduled_at');
            $table->index('unpublish_scheduled_at');

            // If you only want one schedule row per polymorphic model
            $table->unique(['schedulable_id', 'schedulable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('content_schedules');
    }
};