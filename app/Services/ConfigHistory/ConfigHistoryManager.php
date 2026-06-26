<?php

namespace App\Services\ConfigHistory;

use App\Models\Config;
use App\Services\ConfigCompare\ConfigVersionCompareService;
use Illuminate\Support\Facades\DB;

class ConfigHistoryManager
{
    /**
     * @var callable
     */
    protected $compareFactory;

    /**
     * @param  callable|null  $compareFactory  Optional factory returning a comparer (used to inject fakes in tests).
     */
    public function __construct(?callable $compareFactory = null)
    {
        $this->compareFactory = $compareFactory ?: function (Config $config, string $commandName): ConfigVersionCompareService {
            return new ConfigVersionCompareService($config, $commandName);
        };
    }

    /**
     * Handle a newly downloaded config: run version/diff logic in a transaction.
     */
    public function handleNewDownloadedConfig(Config $config, string $commandName): bool
    {
        return DB::transaction(function () use ($config, $commandName): bool {
            $compareSvc = call_user_func($this->compareFactory, $config, $commandName);

            return (bool) $compareSvc->version_compare();
        });
    }
}
