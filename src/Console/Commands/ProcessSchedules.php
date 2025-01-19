<?php

namespace ContraInteractive\ContentScheduler\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Model;

class ProcessSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedules:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled items and update their statuses accordingly';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $this->info("Processing schedules at {$now->toDateTimeString()}");

        // 1. Publish any items that are "scheduled" and whose publish time has arrived or passed.
        $this->publishScheduledItems($now);

        // 2. Unpublish any items that are currently "published" and whose unpublish time has arrived or passed.
        $this->unpublishPublishedItems($now);

        // 3. (Optional) Handle other transitions if your business logic requires it.
        // $this->handleOtherTransitions($now);

        $this->info("Processing completed.");
    }

    /**
     * Publish schedules that are due.
     */
    protected function publishScheduledItems(Carbon $now)
    {
        $this->info("Publishing scheduled items...");

        // Find schedules in SCHEDULED status that have a publish_scheduled_at <= now
        $schedules = Schedule::where('status', ScheduleStatus::SCHEDULED)
            ->whereNotNull('publish_scheduled_at')
            ->where('publish_scheduled_at', '<=', $now)
            ->get();

        foreach ($schedules as $schedule) {
            try {
                // Mark it actually published
                $schedule->status       = ScheduleStatus::PUBLISHED;
                $schedule->published_at = $now;
                // Optionally clear the scheduled date so it doesn't trigger again:
                // $schedule->publish_scheduled_at = null;

                $schedule->save();

                // Perform any additional actions, e.g. calling a model's publish method
                $this->publishContent($schedule);

                $this->info("Published Schedule ID: {$schedule->id}");
                Log::info("Published Schedule ID: {$schedule->id}");
            } catch (\Exception $e) {
                $this->error("Failed to publish Schedule ID: {$schedule->id}. Error: {$e->getMessage()}");
                Log::error("Failed to publish Schedule ID: {$schedule->id}. Error: {$e->getMessage()}");
            }
        }
    }

    /**
     * Unpublish schedules that are due.
     */
    protected function unpublishPublishedItems(Carbon $now)
    {
        $this->info("Unpublishing published items...");

        // Find schedules in PUBLISHED status that have unpublish_scheduled_at <= now
        $schedules = Schedule::where('status', ScheduleStatus::PUBLISHED)
            ->whereNotNull('unpublish_scheduled_at')
            ->where('unpublish_scheduled_at', '<=', $now)
            ->get();

        foreach ($schedules as $schedule) {
            try {
                // Update status to unpublished
                $schedule->status         = ScheduleStatus::UNPUBLISHED;
                $schedule->unpublished_at = $now;
                // Optionally clear the scheduled date so it doesn't trigger again:
                // $schedule->unpublish_scheduled_at = null;

                $schedule->save();

                // Perform any additional actions, e.g. calling a model's unpublish method
                $this->unpublishContent($schedule);

                $this->info("Unpublished Schedule ID: {$schedule->id}");
                Log::info("Unpublished Schedule ID: {$schedule->id}");
            } catch (\Exception $e) {
                $this->error("Failed to unpublish Schedule ID: {$schedule->id}. Error: {$e->getMessage()}");
                Log::error("Failed to unpublish Schedule ID: {$schedule->id}. Error: {$e->getMessage()}");
            }
        }
    }

    /**
     * Publish the associated content (if the model has a publish method).
     */
    protected function publishContent(Schedule $schedule)
    {
        /** @var Model $model */
        $model = $schedule->schedulable;

        if ($model && method_exists($model, 'publish')) {
            $model->publish();
        } else {
            Log::warning("Schedulable model for Schedule ID: {$schedule->id} does not have a publish method.");
        }
    }

    /**
     * Unpublish the associated content (if the model has an unpublish method).
     */
    protected function unpublishContent(Schedule $schedule)
    {
        /** @var Model $model */
        $model = $schedule->schedulable;

        if ($model && method_exists($model, 'unpublish')) {
            $model->unpublish();
        } else {
            Log::warning("Schedulable model for Schedule ID: {$schedule->id} does not have an unpublish method.");
        }
    }

}