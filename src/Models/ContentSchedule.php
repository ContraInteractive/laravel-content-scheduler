<?php

namespace ContraInteractive\ContentScheduler\Models;
use Illuminate\Database\Eloquent\Model;
use ContraInteractive\ContentScheduler\Enums\ScheduleStatus;

class ContentSchedule extends Model
{
    protected $fillable = [
        'schedulable_id',
        'schedulable_type',
        'published_at',
        'unpublished_at',
        'publish_scheduled_at',
        'unpublish_scheduled_at',
        'status',
        'notes',
    ];

    /**
     * Get the parent schedulable model (morph to any model).
     */
    public function schedulable()
    {
        return $this->morphTo();
    }

    /**
     * Cast attributes to native types.
     */
    protected $casts = [
        'published_at' => 'datetime',
        'unpublished_at' => 'datetime',
        'publish_scheduled_at' => 'datetime',
        'unpublish_scheduled_at' => 'datetime',
        'status' => ScheduleStatus::class, // Cast 'status' to ScheduleStatus enum
    ];

    /**
     * Scope a query to only include schedules with a specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param ScheduleStatus $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeStatus($query, ScheduleStatus $status)
    {
        return $query->where('status', $status->value);
    }
}