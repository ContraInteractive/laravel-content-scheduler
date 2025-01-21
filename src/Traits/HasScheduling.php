<?php

namespace ContraInteractive\ContentScheduler\Traits;
use ContraInteractive\ContentScheduler\Facades\Scheduler;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;

trait HasScheduling
{
    /**
     * Schedule publishing for the model.
     *
     * @param mixed $publishAt
     * @param mixed|null $unpublishAt
     * @param string|null $notes
     * @return Schedule
     */
    public function schedulePublish($publishAt, $unpublishAt = null, string $notes = null): Schedule
    {
        return Scheduler::schedulePublish($this, $publishAt, $unpublishAt, $notes);
    }

    /**
     * Schedule unpublishing for the model.
     *
     * @param mixed $unpublishAt
     * @param string|null $notes
     * @return Schedule
     */
    public function scheduleUnpublish($unpublishAt, string $notes = null): Schedule
    {
        return Scheduler::scheduleUnpublish($this, $unpublishAt, $notes);
    }

    /**
     * Cancel a schedule.
     *
     * @return bool
     */
    public function cancelSchedule(): bool
    {
        return Scheduler::cancelSchedule($this);
    }

    /**
     * Clear any scheduled dates for the model. but keep the status.
     *
     * @return bool
     */

    public function clearSchedule()
    {
        return Scheduler::clearSchedule($this);
    }

    /**
     * published the model immediately.
     *
     * @return bool
     */
    public function publish(): bool
    {
        return Scheduler::publish($this);
    }

    /**
     * Unpublish the model immediately.
     *
     * @return bool
     */
    public function unpublish(): bool
    {
        return Scheduler::unpublish($this);
    }



}