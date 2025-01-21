### Installation
You can install the package via composer:

```bash
composer require 'contrainteractive/content-scheduler'
```

Once the package is installed, you should publish the migration files and run the migration:

```bash
php artisan vendor:publish --provider="ContraInteractive\ContentScheduler\Providers\ContentSchedulerServiceProvider" --tag="migrations"
php artisan migrate
```

### The Content Scheduler pkg provides a convenient way to associate and manage scheduling information with any Eloquent model: ###
-	A polymorphic relationship to a ContentSchedule record.
-	Helpful accessors for scheduled/actual publish/unpublish timestamps.
-	Methods to quickly check whether the model is published, unpublished, scheduled, or canceled.
-	Query scopes for filtering by schedule status.
-   Scheduled job/command to publish or unpublish the model at the appropriate time. 

## Usage

Your Eloquent models should use the `ContraInteractive\ContentScheduler\Traits\Schedulable` trait and the `ContraInteractive\ContentScheduler\Traits\HasScheduling` class.

Here's an example of how to implement the traits:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use ContraInteractive\ContentScheduler\Traits\Schedulable;
use ContraInteractive\ContentScheduler\Traits\HasScheduling;

class Post extends Model
{
    use Schedulable;
    use HasScheduling;

    // ...
}
```

### Scheduling a Model
```php
$model = AnyModel::find(1);

Scheduler::schedulePublish($model, '2027-01-23')
    // Optionally, you can set the scheduled unpublish date
    ->scheduleUnpublish($model, '2028-01-25');
```
Then in your `bootstrap/app.php` (assuming Laravel 11.x)
```php

use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
     ->withSchedule(callback: function (Schedule $schedule) {
         $schedule->command('schedules:process')
         ->everyMinute();
     })
    ->create();
```

More information on scheduled commands can be found in the [official documentation](https://laravel.com/docs/11.x/scheduling).

### More Usage Examples
```php
//Publish Immediately
Scheduler::publish($model);
$model->isPublished()  // true.

Scheduler::unpublish($model);
$model->isPublished()  // false.
	
//Schedule Future Publish
Scheduler::schedulePublish($model, '2025-02-01');
$model->isScheduled() // true.

//Schedule Future Unpublish
Scheduler::scheduleUnpublish($model, '2025-02-10');
$model->isScheduledForFutureUnpublish() // true.
```

### Query Scopes
```php
//  ContraInteractive\ContentScheduler\Traits\Schedulable::class

Post::whereScheduleStatus('SCHEDULED')->get();

Post::published()->get();

Post::scheduled()->get();

Post::unpublished()->get();
```