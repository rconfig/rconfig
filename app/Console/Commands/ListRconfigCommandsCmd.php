<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use function Laravel\Prompts\table;
use function Laravel\Prompts\text;

class ListRconfigCommandsCmd extends Command
{
    protected $signature = 'rconfig:list';
    protected $description = 'List all rconfig:* commands with their descriptions';

    public function handle(): int
    {
        $commands = Artisan::all();

        $rows = collect($commands)
            ->filter(fn ($command, $name) => str_starts_with($name, 'rconfig:'))
            ->map(fn ($command, $name) => [
                $name,
                $command->getDescription() ?: '<no description>',
            ])
            ->sortBy(fn ($row) => $row[0])
            ->values()
            ->toArray();

        if (empty($rows)) {
            $this->warn("No rconfig commands found");

            return self::SUCCESS;
        }

        table(
            headers: ['Command', 'Description'],
            rows: $rows
        );

        return self::SUCCESS;
    }
}
