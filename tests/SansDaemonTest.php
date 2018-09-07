<?php

use Orchestra\Testbench\TestCase;
use Queueworker\SansDaemon\Console\WorkCommand;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobProcessing;

class SansDaemonTest extends TestCase
{
    protected $artisan;

    protected $worker;

    public function setUp()
    {
        parent::setUp();

        $this->artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');
        $this->worker = $this->app->make('queue.worker');
    }

    public function testQueueWorkerCanRunInSansDaemonMode()
    {
        $exitCode = $this->artisan->call('queue:work', [
            '--sansdaemon' => true
        ]);

        $this->assertEquals(0, $exitCode);
    }

    public function testQueueWorkerCanFireJob()
    {
        $this->worker->setManager($this->getManager('sync', ['default' => [$job = new FakeWorkerJob]]));

        $exitCode = $this->artisan->call('queue:work', [
            '--sansdaemon' => true
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertTrue($job->fired);
    }

    public function testQueueWorkerCanExecutesNJobs()
    {
        $jobs = [
            'default' => [new FakeWorkerJob, new FakeWorkerJob, new FakeWorkerJob]
        ];

        $this->worker->setManager($this->getManager('sync', $jobs));
        
        $exitCode = $this->artisan->call('queue:work', [
            '--sansdaemon' => true, '--jobs' => 2
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals(1, $this->worker->getManager()->connection('sync')->size('default'));
    }

    public function testQueueWorkerCanExecuteJobsInMultipleQueues()
    {
        $jobs = [
            'high' => [new FakeWorkerJob, new FakeWorkerJob],
            'default' => [new FakeWorkerJob]
        ];

        $this->worker->setManager($this->getManager('sync', $jobs));

        $exitCode = $this->artisan->call('queue:work', [
            '--sansdaemon' => true, '--jobs' => 3, '--queue' => 'high,default'
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals(0, $this->worker->getManager()->connection('sync')->size('high'));
        $this->assertEquals(0, $this->worker->getManager()->connection('sync')->size('default'));
    }

    public function testQueueWorkerExitsAfterMaxExecTime()
    {
        $jobs = [
            'default' => [new FakeWorkerJob(true, 10), new FakeWorkerJob(true)]
        ];

        $this->worker->setManager($this->getManager('sync', $jobs));

        $exitCode = $this->artisan->call('queue:work', [
            '--sansdaemon' => true, '--max_exec_time' => 5
        ]);

        $this->assertEquals(0, $exitCode);
        $this->assertEquals(1, $this->worker->getManager()->connection('sync')->size('default'));
    }

    ######################
    # Helpers 
    ######################
    protected function getPackageProviders($app)
    {
        return ['Queueworker\SansDaemon\SansDaemonServiceProvider'];
    }

    private function getManager($connectionName, $jobs = [])
    {
        return new FakeWorkerManager($connectionName, new FakeWorkerConnection($jobs));
    }

    private function getWorkerOptions()
    {
        return new WorkerOptions;
    }
}

######################
# Fakes 
######################
class FakeWorkerManager extends \Illuminate\Queue\QueueManager
{
    public $connections = [];

    public function __construct($name, $connection)
    {
        $this->connections[$name] = $connection;
    }

    public function connection($name = null)
    {
        return $this->connections[$name];
    }
}

class FakeWorkerConnection
{
    public $jobs = [];

    public function __construct($jobs)
    {
        $this->jobs = $jobs;
    }

    public function pop($queue)
    {
        return array_shift($this->jobs[$queue]);
    }

    public function size($queue)
    {
        return count($this->jobs[$queue]);
    }
}

class FakeWorkerJob extends \Illuminate\Queue\Jobs\Job implements \Illuminate\Contracts\Queue\Job
{
    public $fired = false;
    public $callback;
    public $deleted = false;
    public $releaseAfter;
    public $released = false;
    public $maxTries;
    public $timeoutAt;
    public $attempts = 0;
    public $failedWith;
    public $failed = false;
    public $connectionName;
    public $shouldSleep;
    public $sleepFor;


    public function __construct($shouldSleep = false, $sleepFor = 0)
    {
        $this->shouldSleep = $shouldSleep;
        $this->sleepFor = $sleepFor;
    }

    public function fire()
    {
        $this->fired = true;

        if ($this->shouldSleep) {
            sleep($this->sleepFor);
        }
    }

    public function payload()
    {
        return [];
    }

    public function maxTries()
    {
        return $this->maxTries;
    }

    public function timeoutAt()
    {
        return $this->timeoutAt;
    }

    public function delete()
    {
        $this->deleted = true;
    }

    public function isDeleted()
    {
        return $this->deleted;
    }

    public function release($delay = 0)
    {
        $this->released = true;
        $this->releaseAfter = $delay;
    }

    public function isReleased()
    {
        return $this->released;
    }

    public function attempts()
    {
        return $this->attempts;
    }

    public function markAsFailed()
    {
        $this->failed = true;
    }
    
    public function failed($e)
    {
        $this->markAsFailed();
        $this->failedWith = $e;
    }

    public function hasFailed()
    {
        return $this->failed;
    }

    public function getJobId()
    {
        return '';
    }

    public function getRawBody()
    {
        return [];
    }

    public function resolveName()
    {
        return 'FakeWorkerJob';
    }

    public function setConnectionName($name)
    {
        $this->connectionName = $name;
    }
    
}