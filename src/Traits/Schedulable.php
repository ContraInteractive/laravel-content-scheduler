<?php

namespace ContraInteractive\ContentScheduler\Traits;
use ContraInteractive\ContentScheduler\Models\ContentSchedule;
use ContraInteractive\ContentScheduler\Facades\Scheduler;
use Illuminate\Support\Carbon;

trait Schedulable
{

    public function schedule()
    {
        return $this->morphOne(ContentSchedule::class, 'schedulable');
    }

    /**
     * Get the 'scheduled_at' datetime.
     */
    public function getScheduledAt(): ?Carbon
    {
        return $this->schedule ? $this->schedule->scheduled_at : null;
    }

    /**
     * Get the 'published_at' datetime.
     */
    public function getPublishDate(): ?Carbon
    {
        return $this->schedule ? $this->schedule->published_at : null;
    }

    /**
     * Get the 'unpublished_at' datetime.
     */
    public function getUnpublishDate(): ?Carbon
    {
        return $this->schedule ? $this->schedule->unpublished_at : null;
    }

    /**
     * Is this model scheduled to be published in the future?
     */
    public function isScheduledForFuture(): bool
    {
        // For example, you might consider a future schedule to be one with
        // a `scheduled_at` or `published_at` timestamp that is still in the future.
        if (! $this->schedule) {
            return false;
        }

        // You can decide whether to check `scheduled_at` or `published_at` for your "future" logic
        return $this->schedule->scheduled_at
            && $this->schedule->scheduled_at->isFuture();
    }

    /**
     * Is this model currently published?
     */
    public function isPublished(): bool
    {
        // Some apps track status in a field (e.g., 'published' or 'scheduled'),
        // or you could assume it's published if the current time is past 'published_at'
        if (! $this->schedule) {
            return false;
        }

        return $this->schedule->published_at
            && $this->schedule->published_at->isPast()
            && (
                // No unpublish date OR unpublish date is in the future
                ! $this->schedule->unpublished_at
                || $this->schedule->unpublished_at->isFuture()
            );
    }
}