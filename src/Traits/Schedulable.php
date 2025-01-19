<?php

namespace ContraInteractive\ContentScheduler\Traits;

use ContraInteractive\ContentScheduler\Models\ContentSchedule;
use ContraInteractive\ContentScheduler\Facades\Scheduler; // If needed
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Carbon;

trait Schedulable
{
    /**
     * Polymorphic relationship to the ContentSchedule model.
     */
    public function schedule(): MorphOne
    {
        return $this->morphOne(ContentSchedule::class, 'schedulable');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors for Scheduled/Actual Dates
    |--------------------------------------------------------------------------
    */

    /**
     * Get the scheduled publish date/time.
     */
    public function getPublishScheduledAt(): ?Carbon
    {
        return $this->schedule ? $this->schedule->publish_scheduled_at : null;
    }

    /**
     * Get the scheduled unpublish date/time.
     */
    public function getUnpublishScheduledAt(): ?Carbon
    {
        return $this->schedule ? $this->schedule->unpublish_scheduled_at : null;
    }

    /**
     * Get the actual published_at datetime.
     */
    public function getPublishedAt(): ?Carbon
    {
        return $this->schedule ? $this->schedule->published_at : null;
    }

    /**
     * Get the actual unpublished_at datetime.
     */
    public function getUnpublishedAt(): ?Carbon
    {
        return $this->schedule ? $this->schedule->unpublished_at : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Status Checks
    |--------------------------------------------------------------------------
    */

    /**
     * Determine if this model has a schedule record.
     */
    public function hasSchedule(): bool
    {
        return (bool) $this->schedule;
    }

    /**
     * Is this model currently scheduled (not yet published)?
     */
    public function isScheduled(): bool
    {
        return $this->schedule
            && $this->schedule->status === ScheduleStatus::SCHEDULED;
    }

    /**
     * Is this model currently published?
     */
    public function isPublished(): bool
    {
        return $this->schedule
            && $this->schedule->status === ScheduleStatus::PUBLISHED;
    }

    /**
     * Is this model currently unpublished?
     */
    public function isUnpublished(): bool
    {
        return $this->schedule
            && $this->schedule->status === ScheduleStatus::UNPUBLISHED;
    }

    /**
     * Is this model's schedule canceled?
     */
    public function isCanceled(): bool
    {
        return $this->schedule
            && $this->schedule->status === ScheduleStatus::CANCELED;
    }

    /*
    |--------------------------------------------------------------------------
    | Time-based Checks
    |--------------------------------------------------------------------------
    */

    /**
     * Is this model scheduled to be published in the future (based on 'publish_scheduled_at')?
     */
    public function isScheduledForFuturePublish(): bool
    {
        if (! $this->schedule || ! $this->schedule->publish_scheduled_at) {
            return false;
        }

        return $this->schedule->publish_scheduled_at->isFuture();
    }

    /**
     * Is this model scheduled to be unpublished in the future (based on 'unpublish_scheduled_at')?
     */
    public function isScheduledForFutureUnpublish(): bool
    {
        if (! $this->schedule || ! $this->schedule->unpublish_scheduled_at) {
            return false;
        }

        return $this->schedule->unpublish_scheduled_at->isFuture();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    | These scopes let you filter models based on schedule status.
    | For example, MyModel::published()->get() to find all currently published ones.
    |--------------------------------------------------------------------------
    */

    /**
     * Scope to find models that have a schedule with a given status.
     */
    public function scopeWhereScheduleStatus($query, string $status)
    {
        return $query->whereHas('schedule', function ($q) use ($status) {
            $q->where('status', $status);
        });
    }

    /**
     * Scope for published models.
     */
    public function scopePublished($query)
    {
        return $query->whereHas('schedule', function ($q) {
            $q->where('status', ScheduleStatus::PUBLISHED);
        });
    }

    /**
     * Scope for scheduled (but not yet published) models.
     */
    public function scopeScheduled($query)
    {
        return $query->whereHas('schedule', function ($q) {
            $q->where('status', ScheduleStatus::SCHEDULED);
        });
    }

    /**
     * Scope for unpublished models.
     */
    public function scopeUnpublished($query)
    {
        return $query->whereHas('schedule', function ($q) {
            $q->where('status', ScheduleStatus::UNPUBLISHED);
        });
    }

    /**
     * Scope for canceled schedules.
     */
    public function scopeCanceled($query)
    {
        return $query->whereHas('schedule', function ($q) {
            $q->where('status', ScheduleStatus::CANCELED);
        });
    }
}