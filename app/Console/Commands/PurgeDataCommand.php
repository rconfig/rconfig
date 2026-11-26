<?php

namespace App\Console\Commands;

use App\Enums\NotificationChannel;
use App\Enums\NotificationType;
use App\Notifications\DBPurgeOperationNotification;
use App\Notifications\MailPurgeOperationNotification;
use App\Services\DataPurge\DataPurgeService;
use App\Services\DataPurge\PurgeRunRecorder;
use App\Traits\NotificationDispatcher;
use Illuminate\Console\Command;

/**
 * @phpstan-import-type PurgeResults from DataPurgeService
 * @phpstan-import-type TableResult from DataPurgeService
 */
class PurgeDataCommand extends Command
{
    use NotificationDispatcher;

    protected $signature = 'rconfig:data-purge
                            {--table= : Purge specific table only}
                            {--dry-run : Show what would be purged without deleting}
                            {--queue-threshold=1000 : Records threshold to use queue jobs}
                            {--force-queue : Always use queue jobs}
                            {--no-queue : Never use queue jobs}';
    protected $description = 'Purge old data based on the Data Purge settings';

    public function __construct(private DataPurgeService $purgeService, private PurgeRunRecorder $purgeRunRecorder)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Starting data purge process...');
        $this->newLine();

        $dryRun = (bool) $this->option('dry-run');
        $specificTable = $this->option('table') ?: null;

        $this->configureService($dryRun);

        $run = $this->purgeRunRecorder->start(
            'data_purge',
            ['table' => $specificTable],
            [
                'dry_run' => $dryRun,
                'queue_threshold' => (int) $this->option('queue-threshold'),
                'force_queue' => (bool) $this->option('force-queue'),
                'no_queue' => (bool) $this->option('no-queue'),
            ],
            $dryRun,
            'purge',
            'rconfig:data-purge',
        );

        try {
            $results = $this->purgeService->execute($specificTable);

            $this->purgeRunRecorder->finish($run, $results, empty($results['errors']) ? 'success' : 'completed_with_errors');
        } catch (\Throwable $e) {
            $this->purgeRunRecorder->fail($run, $e);

            throw $e;
        }

        $this->displayResults($results);

        if (! $dryRun) {
            $this->notifyIfAnythingHappened($results);
        }

        return Command::SUCCESS;
    }

    private function configureService(bool $dryRun): void
    {
        if ($dryRun) {
            $this->purgeService->setDryRun(true);
            $this->warn('DRY RUN MODE - No data will be actually deleted');
            $this->newLine();
        }

        $this->purgeService->setQueueThreshold((int) $this->option('queue-threshold'));
        $this->purgeService->setForceQueue((bool) $this->option('force-queue'));
        $this->purgeService->setNoQueue((bool) $this->option('no-queue'));
    }

    /**
     * One notification per run, through the notification preferences. A run that found nothing to do stays quiet.
     *
     * @param  PurgeResults  $results
     */
    private function notifyIfAnythingHappened(array $results): void
    {
        if ($results['total_deleted_directly'] === 0 && $results['total_queued'] === 0 && empty($results['errors'])) {
            return;
        }

        $lines = [];

        foreach ($results['tables_processed'] as $tableResult) {
            if (in_array($tableResult['status'], ['success', 'queued', 'error'], true) && ($tableResult['records_found'] > 0 || $tableResult['status'] === 'error')) {
                $lines[] = "{$tableResult['table']}: {$tableResult['message']}";
            }
        }

        $message = 'Data purge ' . (empty($results['errors']) ? 'completed' : 'completed with errors') . '. ' . implode('. ', $lines) . '.';

        $this->sendToSingleChannel(NotificationType::CONFIG_PURGE_COMPLETED, NotificationChannel::DB, new DBPurgeOperationNotification($message));
        $this->sendToSingleChannel(NotificationType::CONFIG_PURGE_COMPLETED, NotificationChannel::MAIL, new MailPurgeOperationNotification($message));
    }

    /**
     * @param  PurgeResults  $results
     */
    private function displayResults(array $results): void
    {
        if ($results['total_checked'] === 0) {
            $this->warn('No purge settings found.');

            return;
        }

        foreach ($results['tables_processed'] as $tableResult) {
            $this->displayTableResult($tableResult);
        }

        $this->newLine();
        $this->info('Purge Summary:');
        $this->line("Tables checked: <fg=cyan>{$results['total_checked']}</>");
        $this->line("Direct deletions: <fg=green>{$results['total_deleted_directly']}</> records");
        $this->line("Queued jobs: <fg=blue>{$results['total_queued']}</>");

        if (! empty($results['errors'])) {
            $this->newLine();
            $this->error('Errors encountered:');

            foreach ($results['errors'] as $error) {
                $this->line("  <fg=red>✗</> {$error}");
            }
        }
    }

    /**
     * @param  TableResult  $tableResult
     */
    private function displayTableResult(array $tableResult): void
    {
        $message = $tableResult['message'];

        $this->line("Processing table: <fg=cyan>{$tableResult['table']}</>");

        match ($tableResult['status']) {
            'success' => $this->line("  <fg=green>✓</> {$message}"),
            'queued' => $this->line("  <fg=blue>⏳</> {$message}"),
            'dry_run' => $this->line("  <fg=blue>[DRY RUN]</> {$message}"),
            'skipped' => $this->line("  <fg=gray>-</> Skipped: {$message}"),
            'error' => $this->line("  <fg=red>✗</> Error: {$message}"),
            default => $this->line("  <fg=yellow>?</> Unknown status: {$message}"),
        };
    }
}
