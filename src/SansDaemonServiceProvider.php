<?php

namespace Queueworker\SansDaemon;

use Illuminate\Contracts\Debug\ExceptionHandler;
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

        $this->configureQueue();

        $this->registerWorkCommand();
    }

    /**
     * Configure the queue.
     *
     * @return void.
     */
    protected function configureQueue()
    {
        if ($this->app->bound('queue.sansDaemonWorker')) {
            return;
        }

        $this->app->singleton('queue.sansDaemonWorker', function () {
            $isDownForMaintenance = function () {
                return $this->app->isDownForMaintenance();
            };

            return new SansDaemonWorker(
                $this->app['queue'],
                $this->app['events'],
                $this->app[ExceptionHandler::class],
                $isDownForMaintenance
            );
        });
    }

    /**
     * Register the command.
     *
     * @return void
     */
    protected function registerWorkCommand()
    {
        $this->app->extend('command.queue.work', function ($command, Application $app) {
            return new WorkCommand($app['queue.sansDaemonWorker'], $app['cache.store']);
        });
    }
}
