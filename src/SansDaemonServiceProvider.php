<?php

namespace Queueworker\SansDaemon;

use Illuminate\Queue\QueueServiceProvider;
use Queueworker\SansDaemon\Console\WorkCommand;

class SansDaemonServiceProvider extends QueueServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->registerWorkCommand();
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerWorkCommand()
    {
        $this->app->singleton('command.queue.work', function ($app) {
            return new WorkCommand($app['queue.worker']);
        });
    }
}