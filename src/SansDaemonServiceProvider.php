<?php

namespace Queueworker\SansDaemon;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\QueueServiceProvider;
use Queueworker\SansDaemon\Console\WorkCommand;

class SansDaemonServiceProvider extends QueueServiceProvider
{
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
        $this->app->extend('command.queue.work', function ($command, Application $app) {
            return new WorkCommand($app['queue.worker'], $app['cache.store']);
        });
    }
}
