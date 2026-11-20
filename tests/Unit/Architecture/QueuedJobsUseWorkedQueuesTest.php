<?php

use App\Jobs\CheckForUpdateJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\File;

/**
 * Architecture guard (RCO-2244). A queued job with no queue of its own lands on "default", which no
 * Horizon supervisor in config/horizon.php works, so it never runs. Every job sets its queue in its
 * constructor ($this->onQueue('...')) or $queue property, and that queue must be worked by Horizon.
 */

/**
 * Queues worked by a supervisor in every Horizon environment.
 *
 * @return list<string>
 */
function horizonWorkedQueues(): array
{
    $perEnvironment = [];

    foreach ((array) config('horizon.environments', []) as $supervisors) {
        $queues = [];

        foreach ((array) $supervisors as $supervisor) {
            foreach ((array) ($supervisor['queue'] ?? []) as $queue) {
                $queues[] = $queue;
            }
        }

        $perEnvironment[] = array_unique($queues);
    }

    return $perEnvironment === [] ? [] : array_values(array_intersect(...$perEnvironment));
}

/**
 * Every concrete class under app/Jobs that can be queued.
 *
 * @return list<class-string>
 */
function queueableJobClasses(): array
{
    $classes = [];

    foreach (File::allFiles(app_path('Jobs')) as $file) {
        $class = 'App\\Jobs\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

        if (! class_exists($class)) {
            continue;
        }

        $reflection = new ReflectionClass($class);
        $queueable = $reflection->implementsInterface(ShouldQueue::class) || in_array(Queueable::class, class_uses_recursive($class), true);

        if ($queueable && ! $reflection->isAbstract()) {
            $classes[] = $class;
        }
    }

    return $classes;
}

/**
 * The queue a job sets for itself, read from its $queue default or its constructor.
 */
function pinnedQueue(ReflectionClass $reflection): ?string
{
    $default = $reflection->getDefaultProperties()['queue'] ?? null;

    if (is_string($default) && $default !== '') {
        return $default;
    }

    $constructor = $reflection->getConstructor();

    if ($constructor === null || $constructor->getDeclaringClass()->getName() !== $reflection->getName()) {
        return null;
    }

    $lines = array_slice(file($constructor->getFileName()), $constructor->getStartLine() - 1, $constructor->getEndLine() - $constructor->getStartLine() + 1);
    $source = implode('', $lines);

    if (preg_match('/onQueue\(\s*\(string\)\s*config\(\s*[\'"]([^\'"]+)[\'"]/', $source, $match)) {
        return (string) config($match[1]);
    }

    if (preg_match('/onQueue\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $source, $match) || preg_match('/\$this->queue\s*=\s*[\'"]([^\'"]+)[\'"]/', $source, $match)) {
        return $match[1];
    }

    return null;
}

test('Horizon works rConfigDefault in every environment', function () {
    expect(horizonWorkedQueues())->toContain('rConfigDefault');
});

test('job discovery finds the queued jobs', function () {
    expect(queueableJobClasses())->toContain(CheckForUpdateJob::class)
        ->and(count(queueableJobClasses()))->toBeGreaterThanOrEqual(10);
});

test('every queued job sets a queue that Horizon works', function () {
    $worked = horizonWorkedQueues();
    $offenders = [];

    foreach (queueableJobClasses() as $class) {
        $queue = pinnedQueue(new ReflectionClass($class));

        if ($queue === null || ! in_array($queue, $worked, true)) {
            $offenders[] = $class . ' -> ' . ($queue ?? 'no queue set (lands on "default")');
        }
    }

    expect($offenders)->toBeEmpty(
        "These jobs would never run, because no Horizon supervisor works their queue:\n"
        . implode("\n", $offenders)
        . "\nSet one in the constructor, e.g. \$this->onQueue('rConfigDefault'), using a queue listed in config/horizon.php."
    );
});

test('every onQueue() call in app/ names a queue that Horizon works', function () {
    $worked = horizonWorkedQueues();
    $offenders = [];

    foreach (File::allFiles(app_path()) as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        foreach (file($file->getPathname()) as $number => $line) {
            if (preg_match('#^\s*(//|\*|/\*|\#)#', $line)) {
                continue;
            }

            if (preg_match_all('/onQueue\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $line, $matches)) {
                foreach ($matches[1] as $queue) {
                    if (! in_array($queue, $worked, true)) {
                        $offenders[] = $file->getRelativePathname() . ':' . ($number + 1) . ' -> ' . $queue;
                    }
                }
            }
        }
    }

    expect($offenders)->toBeEmpty("These dispatches name a queue no Horizon supervisor works:\n" . implode("\n", $offenders));
});
