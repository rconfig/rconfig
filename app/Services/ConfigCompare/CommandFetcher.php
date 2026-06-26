<?php

namespace App\Services\ConfigCompare;

use App\Models\Command;
use App\Models\Config;

class CommandFetcher
{
    public function __construct(private Config $model, private string $commandName) {}

    public function getCommand(): ?Command
    {
        return Command::where('command', $this->commandName)->first();
    }
}
