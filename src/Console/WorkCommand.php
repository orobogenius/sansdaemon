<?php

namespace Queueworker\SansDaemon\Console;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;
use Illuminate\Queue\Worker;
use Queueworker\SansDaemon\Traits\SansDaemonWorkerTrait;

class WorkCommand extends BaseWorkCommand
{
    use SansDaemonWorkerTrait;

    /**
     * Create a new queue work command.
     *
     * @param \Illuminate\Queue\Worker $worker
     *
     * @return void
     */
    public function __construct(Worker $worker, Cache $cache)
    {
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }

        // Get default max execution time - 5s
        $maxExecutionTime = ini_get('max_execution_time');
        $maxExecutionTime = $maxExecutionTime <= 0 ? 0 : $maxExecutionTime - 5;

        $this->signature .= '{--sansdaemon : Run the worker without a daemon}
                             {--jobs=0 : Number of jobs to process before worker exits}
                             {--max_exec_time='.$maxExecutionTime.' : Maximum seconds to run to prevent error (0 - forever)}';

        $this->description .= ' or sans-daemon';

        parent::__construct($worker, $cache);
    }

    /**
     * Run the worker instance.
     *
     * @param  string $connection
     * @param  string $queue
     *
     * @return array
     */
    protected function runWorker($connection, $queue)
    {
        if ($this->option('sansdaemon')) {
            $this->worker->setCache($this->laravel['cache']->driver());

            return $this->runSansDaemon($connection, $queue);
        }

        parent::runWorker($connection, $queue);
    }

    /**
     * Gather all of the queue worker options as a single object.
     *
     * @return \Illuminate\Queue\WorkerOptions
     */
    protected function gatherWorkerOptions()
    {
        $options = parent::gatherWorkerOptions();
        $options->jobs = $this->option('jobs');
        $options->maxExecutionTime = intval($this->option('max_exec_time'));

        return $options;
    }
}
