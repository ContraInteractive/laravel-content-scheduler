<?php

namespace ContraInteractive\ContentScheduler\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
class SchedulePublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $schedule;

    /**
     * Create a new event instance.
     *
     * @param Schedule $schedule
     */
    public function __construct(Schedule $schedule)
    {
        $this->schedule = $schedule;
    }
}