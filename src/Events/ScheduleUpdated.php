<?php

namespace ContraInteractive\ContentScheduler\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use Illuminate\Database\Eloquent\Model;

class ScheduleUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Schedule $schedule;
    public Model $model;

    /**
     * Create a new event instance.
     *
     * @param Schedule $schedule
     */
    public function __construct(Model $model, Schedule $schedule)
    {
        $this->schedule = $schedule;
        $this->model = $model;
    }
}