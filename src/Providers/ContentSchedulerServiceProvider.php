<?php

namespace ContraInteractive\ContentScheduler\Providers;

use Illuminate\Support\ServiceProvider;
use ContraInteractive\ContentScheduler\Services\SchedulingService;

class ContentSchedulerServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton('scheduling', function ($app) {
            return new SchedulingService();
        });
    }

    public function boot()
    {
        //$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        //$this->loadViewsFrom(__DIR__ . '/../resources/views', 'content-scheduler');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Publish migrations
        //php artisan vendor:publish --provider="ContraInteractive\ContentScheduler\Providers\ContentSchedulerServiceProvider" --tag="migrations"
        $this->publishes([
            __DIR__.'/../database/migrations/create_content_schedules_table.php' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_content_schedules_table.php'),
        ], 'migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \ContraInteractive\ContentScheduler\Console\Commands\ProcessSchedules::class,
            ]);
        }
    }
}