<?php

namespace ContraInteractive\ContentScheduler\Services;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use ContraInteractive\ContentScheduler\Events\ScheduleCreated;
use ContraInteractive\ContentScheduler\Events\ScheduleCanceled;

class SchedulingService
{
    /**
     * Schedule a model for publishing.
     *
     * @param Model $model The schedulable model instance.
     * @param Carbon|string $publishAt The datetime to publish.
     * @param Carbon|string|null $unpublishAt The datetime to unpublish (optional).
     * @param string|null $notes Any additional notes (optional).
     * @return Schedule
     */
    public function schedulePublish(Model $model, $publishAt, $unpublishAt = null, string $notes = null): Schedule
    {
        $publishAt = $this->parseDateTime($publishAt);
        if ($unpublishAt) {
            $unpublishAt = $this->parseDateTime($unpublishAt);
            if ($unpublishAt->lessThanOrEqualTo($publishAt)) {
                throw new \InvalidArgumentException('Unpublish time must be after publish time.');
            }
        }

        $schedule =  $model->schedules()->create([
            'scheduled_at' => $this->parseDateTime($publishAt),
            'published_at' => $this->parseDateTime($publishAt),
            'unpublished_at' => $unpublishAt ? $this->parseDateTime($unpublishAt) : null,
            'status' => ScheduleStatus::SCHEDULED,
            'notes' => $notes,
        ]);

        event(new ScheduleCreated($schedule));

        return $schedule;
    }

    /**
     * Schedule a model for unpublishing.
     *
     * @param Model $model The schedulable model instance.
     * @param Carbon|string $unpublishAt The datetime to unpublish.
     * @param string|null $notes Any additional notes (optional).
     * @return Schedule
     */
    public function scheduleUnpublish(Model $model, $unpublishAt, string $notes = null): Schedule
    {
        return $model->schedules()->create([
            'unpublished_at' => $this->parseDateTime($unpublishAt),
            'status' => ScheduleStatus::SCHEDULED, // Or another appropriate status
            'notes' => $notes,
        ]);
    }

    /**
     * Parse the datetime input to a Carbon instance.
     *
     * @param Carbon|string $dateTime
     * @return Carbon
     */
    protected function parseDateTime($dateTime): Carbon
    {
        return $dateTime instanceof Carbon ? $dateTime : Carbon::parse($dateTime);
    }

    /**
     * Cancel a scheduled item.
     *
     * @param Schedule $schedule
     * @return bool
     */
    public function cancelSchedule(Schedule $schedule): bool
    {
        if ($schedule->status === ScheduleStatus::SCHEDULED) {
            $schedule->status = ScheduleStatus::CANCELED;
            $saved = $schedule->save();

            if($saved) {
                event(new ScheduleCanceled($schedule));
            }

            return $saved;
        }

        return false;
    }

    /**
     * Publish the associated content.
     *
     * @param Schedule $schedule
     */
    public function publish(Schedule $schedule): bool
    {
        $schedule->status = ScheduleStatus::PUBLISHED;
        $schedule->published_at = now();
        $saved = $schedule->save();

        if($saved) {
            event(new SchedulePublished($schedule));
        }

        return $saved;
    }

    /**
     * Unpublish the associated content.
     *
     * @param Schedule $schedule
     */
    public function unpublish(Schedule $schedule): bool
    {
        $schedule->status = ScheduleStatus::UNPUBLISHED;
        $schedule->unpublished_at = now();
        $saved = $schedule->save();

        if($saved) {
            event(new ScheduleUnpublished($schedule));
        }

        return $saved;
    }
}