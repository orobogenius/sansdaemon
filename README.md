# SansDaemon

Batch process Laravel Queue without a daemon; Processes all jobs on the queue(s) and exits without running on daemon mode. This is useful in cases where you just want to process jobs on the queue and exit the worker process so they don't pile up in memory.

## Installation

To install the latest version of SansDaemon, simply use composer

### Download

```
composer require queueworker/sansdaemon
```

- If your Laravel version is below 5.5, you'll need to add the service provider to your ```config/app.php``` file.

```php
Queueworker\SansDaemon\SansDaemonServiceProvider::class,
```

## Usage
SansDaemon is a console application that extends the functionality of laravel's `WorkCommand` - ```Illuminate\Queue\Console\WorkCommand```. _See_ [Laravel Queue](https://laravel.com/docs/5.6/queues) documentation.

To run the queue worker sans-daemon mode, simply add the ```--sansdaemon``` option to the original laravel queue worker command:

```
php artisan queue:work --sansdaemon
```

## Argument and Options
Since this package extends laravel's `WorkCommand`, it takes exactly all the arguments and options the original WorkCommand takes with two added options:

- `--sansdaemon` option tell the worker to process jobs on the queue without running in daemon mode.
- `--jobs` (default: 0, optional) - It allows you to specify the number of jobs to process each time the command runs. The default value `0` means it'll process all available jobs in the queue.

## Testing
// TODO

## License

MIT license (MIT) - Check out the [License File](LICENSE) for more.