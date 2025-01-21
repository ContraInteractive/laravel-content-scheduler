<?php

namespace ContraInteractive\ContentScheduler\Facades;

use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use ContraInteractive\ContentScheduler\Services\SchedulingService;
use ContraInteractive\ContentScheduler\Models\ContentSchedule as Schedule;
use Illuminate\Support\Carbon;

/**
 * @method static SchedulingService schedulePublish(\Illuminate\Database\Eloquent\Model $model, $publishAt, $unpublishAt = null, string $notes = null)
 * @method static SchedulingService scheduleUnpublish(\Illuminate\Database\Eloquent\Model $model, $unpublishAt, string $notes = null)
 * @method static bool cancelSchedule(Model $model)
 * @method static bool clearSchedule(Model $model)
 * @method static bool publish(Model $model)
 * @method static bool unpublish(Model $model)
 * @method static null|Carbon getPublishScheduledAt(Model $model)
 * @method static null|Carbon getUnpublishScheduledAt(Model $model)
 * @method static null|Carbon getPublishedAt(Model $model)
 * @method static null|Carbon getUnpublishedAt(Model $model)
 * @method static bool hasSchedule(Model $model)
 * @method static bool isPublished(Model $model)
 * @method static bool isScheduled(Model $model)
 *
 * @see SchedulingService
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