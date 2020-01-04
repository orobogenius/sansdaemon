<?php

namespace Queueworker\SansDaemon\Traits;

trait SansDaemonWorkerTrait
{
    /**
     * Number of jobs processed.
     *
     * @var int
     */
    protected $jobsProcessed = 0;

    /**
     * Process the queue sans-daemon mode.
     *
     * @param string $connection
     * @param string $queue
     *
     * @return void
     */
    protected function runSansDaemon($connection, $queue)
    {
        return $this->processJobs($connection, $queue);
    }

    /**
     * Process jobs from the queue.
     *
     * @param string $connectionName
     * @param string $queue
     *
     * @throws \Throwable
     *
     * @return void
     */
    public function processJobs($connectionName, $queue)
    {
        foreach (explode(',', $queue) as $queue) {
            while ($this->shouldRunNextJob() && ! is_null($job = $this->getNextJob($connectionName, $queue))) {
                $this->worker->runSansDaemonJob($job, $connectionName, parent::gatherWorkerOptions());

                if ($this->option('jobs')) {
                    $this->jobsProcessed += 1;
                }
            }
        }
    }

    /**
     * Determine if the next job should be processed.
     *
     * @return bool
     */
    protected function shouldRunNextJob()
    {
        if ($this->isOverMaxExecutionTime($this->gatherWorkerOptions())) {
            return false;
        }

        if ($jobs = (int) $this->option('jobs')) {
            return $this->jobsProcessed != $jobs;
        }

        return true;
    }

    /**
     * Detect if the worker is running longer than the maximum execution time.
     *
     * @param \Illuminate\Queue\WorkerOptions $options
     *
     * @return bool
     */
    protected function isOverMaxExecutionTime($options)
    {
        if ($options->maxExecutionTime <= 0) {
            return false;
        }

        $elapsedTime = microtime(true) - LARAVEL_START;

        return $elapsedTime > $options->maxExecutionTime;
    }

    /**
     * Get the next available job from the given queue.
     *
     * @param string $connectionName
     * @param string $queue
     *
     * @return \Illuminate\Contracts\Queue\Job|null
     */
    protected function getNextJob($connectionName, $queue)
    {
        return $this->worker->getNextSansDaemonJob(
            $this->getConnection($connectionName), $queue
        );
    }

    /**
     * Get the queue connection.
     *
     * @param string|null $name
     * @return \Illuminate\Contracts\Queue\Queue
     */
    protected function getConnection($name)
    {
        return $this->worker->getManager()->connection($name);
    }
}
