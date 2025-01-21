<?php

namespace ContraInteractive\ContentScheduler\Services;

use Carbon\Carbon;
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;
use ContraInteractive\ContentScheduler\Events\ScheduleCanceled;
use ContraInteractive\ContentScheduler\Events\ScheduleCreated;
use ContraInteractive\ContentScheduler\Events\SchedulePublished;
use ContraInteractive\ContentScheduler\Events\ScheduleUnpublished;
use ContraInteractive\ContentScheduler\Events\ScheduleUpdated;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use Illuminate\Database\Eloquent\Model;

class SchedulingService
{
    /**
     * Schedule a model to be published at a certain time, with an optional unpublish time.
     *
     * @param Model $model The model instance to schedule.
     * @param Carbon|string $publishAt Date/time to publish the model (scheduled).
     * @param Carbon|string|null $unpublishAt Date/time to unpublish (scheduled), optional.
     * @param string|null $notes Additional notes (optional).
     *
     * @throws \InvalidArgumentException if $unpublishAt <= $publishAt.
     */
    public function schedulePublish(
        Model   $model,
                $publishAt,
        ?string $notes = null
    ): SchedulingService
    {
        $publishAt = $this->parseDateTime($publishAt);


        // Find existing schedule or create a new one
        $schedule = $model->schedule()->first();

        $data = [
            'publish_scheduled_at' => $publishAt,

            // Reset actual times because we haven't published/unpublished yet
            'published_at' => null,
            'unpublished_at' => null,

            // Update status to "scheduled"
            'status' => ScheduleStatus::SCHEDULED,
            'notes' => $notes,
        ];

        if ($schedule) {

            $schedule->update($data);
            $schedule->refresh();

            event(new ScheduleUpdated($model, $schedule));
        } else {
            $schedule = $model->schedule()->create($data);

            event(new ScheduleCreated($model, $schedule));
        }

        return $this;
    }

    /**
     * Schedule a model to be unpublished at a certain time.
     *
     * @param Model $model The model instance to schedule.
     * @param Carbon|string $unpublishAt Date/time to unpublish the model (scheduled).
     * @param string|null $notes Additional notes (optional).
     */
    public function scheduleUnpublish(
        Model   $model,
                $unpublishAt,
        ?string $notes = null
    ): SchedulingService
    {
        $unpublishAt = $this->parseDateTime($unpublishAt);

        // Find existing schedule or create a new one
        $schedule = $model->schedule()->first();

        $data = [
            'unpublish_scheduled_at' => $unpublishAt,

            // Reset the actual unpublish time
            'unpublished_at' => null,
            'notes' => $notes,
        ];

        if ($schedule) {
            $schedule->update($data);
            $schedule->refresh();

            event(new ScheduleUpdated($model, $schedule));
        } else {
            $schedule = $model->schedule()->create($data);

            event(new ScheduleCreated($model, $schedule));
        }

        return $this;
    }

    /**
     * Publish a model immediately.
     * If no schedule exists, create one; otherwise, just update the schedule.
     *
     * @param Model $model
     * @return bool  True on success; otherwise, false.
     */
    public function publish(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        // If no schedule is found, create a new one
        if (!$schedule) {
            $schedule = $this->schedulePublish($model, now());
        }

        // If it's already published, we're done
        if ($schedule->status === ScheduleStatus::PUBLISHED) {
            return true;
        }


        if($schedule->status === ScheduleStatus::UNPUBLISHED || $schedule->status === ScheduleStatus::SCHEDULED) {
            // Mark it as published now
            $schedule->status = ScheduleStatus::PUBLISHED;
            $schedule->published_at = now();
            $schedule->publish_scheduled_at = now();

            $saved = $schedule->save();

            if ($saved) {
                event(new SchedulePublished($model, $schedule));
            }
            return $saved;
        }


        return false;
    }

    /**
     * Unpublish a model immediately by updating its schedule to UNPUBLISHED status.
     *
     * @param Model $model
     * @return bool  True on success; otherwise, false.
     */
    public function unpublish(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        // If there's no schedule, we can't unpublish
        if (!$schedule) {
            return false;
        }

        // If already unpublished, there's nothing to do
        if ($schedule->status === ScheduleStatus::UNPUBLISHED) {
            return true;
        }

        if($schedule->status === ScheduleStatus::PUBLISHED) {
            // Mark it as unpublished now
            $schedule->status = ScheduleStatus::UNPUBLISHED;
            $schedule->unpublished_at = now();
            $schedule->unpublish_scheduled_at = now();

            $saved = $schedule->save();

            if ($saved) {
                event(new ScheduleUnpublished($model, $schedule));
            }
            return $saved;
        }


        return false;

    }

    /**
     * Cancel a schedule if it is currently marked as SCHEDULED.
     *
     * @param Model $model
     * @return bool True on success; otherwise, false.
     */
    public function cancelSchedule(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        // Only allow cancel if it is in the "scheduled" state
        if (!$schedule || $schedule->status !== ScheduleStatus::SCHEDULED) {
            return false;
        }

        $schedule->status = ScheduleStatus::CANCELED;

        // Optionally clear out scheduled times
        $schedule->publish_scheduled_at = null;
        $schedule->unpublish_scheduled_at = null;


        $saved = $schedule->save();

        if ($saved) {
            event(new ScheduleCanceled($model, $schedule));
        }

        return $saved;
    }

    public function clearSchedule(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        if (!$schedule) {
            return false;
        }

        $schedule->unpublish_scheduled_at = null;
        $schedule->publish_scheduled_at = null;

        return $schedule->save();
    }

    /**
     * Convert a mixed date/time input into a Carbon instance.
     *
     * @param Carbon|string $dateTime
     */
    protected function parseDateTime($dateTime): Carbon
    {
        return $dateTime instanceof Carbon
            ? $dateTime
            : Carbon::parse($dateTime);
    }
}