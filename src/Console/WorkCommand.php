<?php

namespace Queueworker\SansDaemon\Console;

use Illuminate\Queue\Console\WorkCommand as BaseWorkCommand;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Worker;
use Queueworker\SansDaemon\WorkerOptions;
use Queueworker\SansDaemon\Traits\SansDaemonWorkerTrait;

class WorkCommand extends BaseWorkCommand
{
    use SansDaemonWorkerTrait;

    /**
     * Create a new queue work command.
     *
     * @param  \Illuminate\Queue\Worker $worker
     * @return void
     */
    public function __construct(Worker $worker)
    {
        $this->signature .= '{--sansdaemon : Run the worker without a daemon}
                             {--jobs=0 : Number of jobs to process before worker exits}';
                    
        $this->description .= ' or sans-daemon';

        parent::__construct($worker);
    }

    /**
     * Run the worker instance.
     *
     * @param  string  $connection
     * @param  string  $queue
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
        
        return $options;
    }
}