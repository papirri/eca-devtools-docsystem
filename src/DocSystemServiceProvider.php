<?php

declare(strict_types=1);

namespace Devtools\DocSystem;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Devtools\DocSystem\Livewire\DocSystemPanel;
use Devtools\DocSystem\Commands\PurgeCommand;

class DocSystemServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/docsystem.php',
            'docsystem'
        );
    }

    public function boot(): void
    {
        // Never run anything in production
        if ($this->app->environment('production')) {
            return;
        }

        $this->registerPublishables();
        $this->registerViews();
        $this->registerCommands();
        $this->registerLivewireComponents();
    }

    protected function registerPublishables(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'docsystem-migrations');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/docsystem.php' => config_path('docsystem.php'),
        ], 'docsystem-config');

        // Publish views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/docsystem'),
        ], 'docsystem-views');
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'docsystem');
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                PurgeCommand::class,
            ]);
        }
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('docsystem-panel', DocSystemPanel::class);
    }
}
