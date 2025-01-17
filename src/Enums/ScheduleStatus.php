<?php

namespace ContraInteractive\ContentScheduler\Enums;

enum ScheduleStatus: string
{
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case UNPUBLISHED = 'unpublished';
    case CANCELED = 'canceled';
}