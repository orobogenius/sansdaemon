<?php

namespace Queueworker\SansDaemon;

use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;

class SansDaemonWorker extends Worker
{
    /**
     * Get the next job from the queue connection.
     *
     * @param  \Illuminate\Contracts\Queue\Queue  $connection
     * @param  string  $queue
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    public function getNextSansDaemonJob($connection, $queue)
    {
        return $this->getNextJob($connection, $queue);
    }

    /**
     * Process the given job.
     *
     * @param  \Illuminate\Contracts\Queue\Job  $job
     * @param  string  $connectionName
     * @param  \Illuminate\Queue\WorkerOptions  $options
     * @return void
     */
    public function runSansDaemonJob($job, $connectionName, WorkerOptions $options)
    {
        return $this->runJob($job, $connectionName, $options);
    }
}
