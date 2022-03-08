<?php

namespace Queueworker\SansDaemon;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;
use Illuminate\Queue\QueueServiceProvider;
use Queueworker\SansDaemon\Console\WorkCommand;

class SansDaemonServiceProvider extends QueueServiceProvider
{
    /**
     * List of supported base commands.
     *
     * @var array
     */
    protected $supportedBaseCommands = [
        BaseWorkCommand::class,
        'command.queue.work',
    ];

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
        // We'll go through the list of known supported queue commands and
        // extend them if they've been bound to the container.
        foreach ($this->supportedBaseCommands as $baseCommand) {
            if ($this->app->bound($baseCommand)) {
                $this->app->extend($baseCommand, function ($command, Application $app) {
                    return new WorkCommand($app['queue.sansDaemonWorker'], $app['cache.store']);
                });

                break;
            }
        }
    }
}
