<?php

namespace ContraInteractive\ContentScheduler\Services;

use Carbon\Carbon;
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;
use ContraInteractive\ContentScheduler\Events\ScheduleCanceled;
use ContraInteractive\ContentScheduler\Events\ScheduleCreated;
use ContraInteractive\ContentScheduler\Events\SchedulePublished;
use ContraInteractive\ContentScheduler\Events\ScheduleUnpublished;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use Illuminate\Database\Eloquent\Model;
use ContraInteractive\ContentScheduler\Events\ScheduleUpdated;

class SchedulingService
{
    /**
     * Schedule a model to be published at a certain time, with an optional unpublish time.
     *
     * @param  Model  $model  The model instance to schedule.
     * @param  Carbon|string  $publishAt  Date/time to publish the model.
     * @param  Carbon|string|null  $unpublishAt  Date/time to unpublish the model (optional).
     * @param  string|null  $notes  Additional notes or comments (optional).
     *
     * @throws \InvalidArgumentException If $unpublishAt is not after $publishAt.
     */
    public function schedulePublish(
        Model $model,
        $publishAt,
        $unpublishAt = null,
        ?string $notes = null
    ): Schedule {
        $publishAt = $this->parseDateTime($publishAt);

        if ($unpublishAt) {
            $unpublishAt = $this->parseDateTime($unpublishAt);

            if ($unpublishAt->lessThanOrEqualTo($publishAt)) {
                throw new \InvalidArgumentException(
                    'Unpublish time must be strictly after the publish time.'
                );
            }
        }

        $schedule = $model->schedule()->first();

        if($schedule){
            // update schedule
            $schedule->update([
                'scheduled_at' => $publishAt,
                'published_at' => null,
                'unpublished_at' => $unpublishAt,
                'status' => ScheduleStatus::SCHEDULED,
                'notes' => $notes,
            ]);

            $schedule->refresh();

            event(new ScheduleUpdated($model, $schedule));

        } else {

            $schedule = $model->schedule()->create([
                'scheduled_at' => $publishAt,
                'published_at' => null,
                'unpublished_at' => $unpublishAt,
                'status' => ScheduleStatus::SCHEDULED,
                'notes' => $notes,
            ]);

            event(new ScheduleCreated($model, $schedule));
        }


        return $schedule;
    }


    /**
     * Schedule a model to be unpublished at a certain time.
     *
     * @param  Model  $model  The model instance to schedule.
     * @param  Carbon|string  $unpublishAt  Date/time to unpublish the model.
     * @param  string|null  $notes  Additional notes or comments (optional).
     */
    public function scheduleUnpublish(
        Model $model,
        $unpublishAt,
        ?string $notes = null
    ): Schedule {
        $unpublishAt = $this->parseDateTime($unpublishAt);

        return $model->schedule()->create([
            'unpublished_at' => null,
            'status' => ScheduleStatus::SCHEDULED,
            'notes' => $notes,
        ]);
    }

    /**
     * Convert a mixed date/time input into a Carbon instance.
     *
     * @param  Carbon|string  $dateTime
     */
    protected function parseDateTime($dateTime): Carbon
    {
        return $dateTime instanceof Carbon
            ? $dateTime
            : Carbon::parse($dateTime);
    }

    /**
     * Cancel a schedule if it is currently marked as SCHEDULED.
     *
     * @param  Schedule  $schedule  The schedule to be canceled.
     * @return bool True on success; otherwise, false.
     */
    public function cancelSchedule(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        if ($schedule->status === ScheduleStatus::SCHEDULED) {
            $schedule->status = ScheduleStatus::CANCELED;
            $saved = $schedule->save();

            if ($saved) {
                event(new ScheduleCanceled($model, $schedule));
            }

            return $saved;
        }

        return false;
    }

    /**
     * Publish a model immediately. If no schedule exists, create one. Otherwise, just update the status.
     *
     * @param  Model  $model  The model to be published.
     * @return bool True on success; otherwise, false.
     */
    public function publish(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        // If no schedule is found, create one for "now"
        if (! $schedule) {
            $schedule = $this->schedulePublish($model, now());
        }

        // If already published, there's nothing to do
        if ($schedule->status === ScheduleStatus::PUBLISHED) {
            return true;
        }

        $schedule->status = ScheduleStatus::PUBLISHED;
        $schedule->published_at = now();
        $saved = $schedule->save();

        if ($saved) {
            event(new SchedulePublished($model, $schedule));
        }

        return $saved;
    }

    /**
     * Unpublish a model by updating its schedule to UNPUBLISHED status.
     *
     * @param  Model  $model  The model to be unpublished.
     * @return bool True on success; otherwise, false.
     */
    public function unpublish(Model $model): bool
    {
        $schedule = $model->schedule()->first();

        // Cannot unpublish if no schedule exists
        if (! $schedule) {
            return false;
        }

        // If already unpublished, there's nothing to do
        if ($schedule->status === ScheduleStatus::UNPUBLISHED) {
            return true;
        }

        $schedule->status = ScheduleStatus::UNPUBLISHED;
        $schedule->unpublished_at = now();
        $saved = $schedule->save();

        if ($saved) {
            event(new ScheduleUnpublished($model, $schedule));
        }

        return $saved;
    }
}
