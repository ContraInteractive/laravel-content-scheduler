<?php

namespace ContraInteractive\ContentScheduler\Traits;
use ContraInteractive\ContentScheduler\Models\ContentSchedule;
use ContraInteractive\ContentScheduler\Facades\Scheduler;

trait Schedulable
{
    /**
     * Get all of the model's schedules.
     */
    public function schedules()
    {
        return $this->morphMany(ContentSchedule::class, 'schedulable');
    }

    /**
     * Schedule the model.
     */
    public function schedule(array $attributes)
    {
        return $this->schedules()->create($attributes);
    }

    public function publish()
    {
        return Scheduler::publish($this);
    }

    public function unpublish()
    {
        return Scheduler::unpublish($this);
    }
}