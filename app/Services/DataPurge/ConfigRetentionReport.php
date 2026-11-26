<?php

namespace App\Services\DataPurge;

class ConfigRetentionReport
{
    public int $totalConfigsConsidered = 0;
    public int $totalConfigsToDelete = 0;
    public int $totalConfigsDeleted = 0;
    public int $totalConfigChangesToDelete = 0;
    public int $totalConfigChangesDeleted = 0;
    public int $totalNonDownloadedConfigsToDelete = 0;
    public int $totalNonDownloadedConfigsDeleted = 0;
    public int $totalFilesDeleted = 0;

    /** @var list<int> */
    public array $configIdsToDelete = [];

    /** @var list<int> */
    public array $configChangeIdsToDelete = [];

    /** @var list<int> */
    public array $nonDownloadedConfigIdsToDelete = [];

    public function totalRecordsToDelete(): int
    {
        return $this->totalConfigsToDelete + $this->totalConfigChangesToDelete + $this->totalNonDownloadedConfigsToDelete;
    }

    public function totalRecordsDeleted(): int
    {
        return $this->totalConfigsDeleted + $this->totalConfigChangesDeleted + $this->totalNonDownloadedConfigsDeleted;
    }
}
