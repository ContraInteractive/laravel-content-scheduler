<?php

namespace ContraInteractive\ContentScheduler\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \App\Models\Schedule schedulePublish(\Illuminate\Database\Eloquent\Model $model, $publishAt, $unpublishAt = null, string $notes = null)
 * @method static \App\Models\Schedule scheduleUnpublish(\Illuminate\Database\Eloquent\Model $model, $unpublishAt, string $notes = null)
 * @method static bool cancelSchedule(\App\Models\Schedule $schedule)
 * @method static bool scheduleForever(Model $model, string $notes = null)
 * @method static bool publish(Model $model)
 * @method static bool unpublish(Model $model)
 * @method static bool isScheduled(Model $model)
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