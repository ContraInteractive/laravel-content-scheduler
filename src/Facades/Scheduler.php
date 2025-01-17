<?php

namespace ContraInteractive\ContentScheduler\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \App\Models\Schedule schedulePublish(\Illuminate\Database\Eloquent\Model $model, $publishAt, $unpublishAt = null, string $notes = null)
 * @method static \App\Models\Schedule scheduleUnpublish(\Illuminate\Database\Eloquent\Model $model, $unpublishAt, string $notes = null)
 * @method static bool cancelSchedule(\App\Models\Schedule $schedule)
 * @method static bool publish(\App\Models\Schedule $schedule)
 * @method static bool unpublish(\App\Models\Schedule $schedule)
 *
 * @see \App\Services\SchedulingService
 */
class Scheduler extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'scheduling';
    }
}