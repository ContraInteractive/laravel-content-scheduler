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

        // 1. Publish scheduled items
        $this->publishScheduledItems($now);

        // 2. Unpublish published items
        $this->unpublishPublishedItems($now);

        // 3. (Optional) Handle other status transitions
        // $this->handleOtherTransitions($now);

        $this->info("Processing completed.");
    }

    /**
     * Publish schedules that are due.
     */
    protected function publishScheduledItems(Carbon $now)
    {
        $this->info("Publishing scheduled items...");

        $schedules = Schedule::where('status', ScheduleStatus::SCHEDULED)
            ->where('scheduled_at', '<=', $now)
            ->get();

        foreach ($schedules as $schedule) {
            try {
                // Update status to published
                $schedule->status = ScheduleStatus::PUBLISHED;
                $schedule->published_at = $now;
                $schedule->save();

                // Perform any additional actions, e.g., publishing the content
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

        $schedules = Schedule::where('status', ScheduleStatus::PUBLISHED)
            ->where('unpublished_at', '<=', $now)
            ->get();

        foreach ($schedules as $schedule) {
            try {
                // Update status to unpublished
                $schedule->status = ScheduleStatus::UNPUBLISHED;
                $schedule->unpublished_at = $now;
                $schedule->save();

                // Perform any additional actions, e.g., unpublishing the content
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
     * Publish the associated content.
     *
     * @param Schedule $schedule
     */
    protected function publishContent(Schedule $schedule)
    {
        /** @var Model $model */
        $model = $schedule->schedulable;

        if ($model && method_exists($model, 'publish')) {
            $model->publish();
        } else {
            // Handle if the method does not exist
            Log::warning("Schedulable model for Schedule ID: {$schedule->id} does not have a publish method.");
        }
    }

    /**
     * Unpublish the associated content.
     *
     * @param Schedule $schedule
     */
    protected function unpublishContent(Schedule $schedule)
    {
        /** @var Model $model */
        $model = $schedule->schedulable;

        if ($model && method_exists($model, 'unpublish')) {
            $model->unpublish();
        } else {
            // Handle if the method does not exist
            Log::warning("Schedulable model for Schedule ID: {$schedule->id} does not have an unpublish method.");
        }
    }

    /**
     * (Optional) Handle other status transitions.
     *
     * @param Carbon $now
     */
    protected function handleOtherTransitions(Carbon $now)
    {
        // Example: Handle canceled schedules if needed
    }
}