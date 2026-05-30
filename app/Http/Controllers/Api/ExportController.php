<?php

namespace App\Http\Controllers\Api;

use App\Exports\ModelExport;
use App\Http\Controllers\Controller;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    use RespondsWithHttpStatus;

    /**
     * @var array<int, string>
     */
    public const EXCLUDED_TABLES = [
        'api_connection_api_credential',
        'api_connection_api_endpoint',
        'api_connection_category',
        'api_connection_tag',
        'api_connection_vendor',
        'api_task',
        'category_command',
        'category_device',
        'category_task',
        'device_tag',
        'device_task',
        'device_template',
        'device_vendor',
        'failed_jobs',
        'health_check_result_history_items',
        'jobs',
        'job_batches',
        'migrations',
        'monitored_scheduled_tasks',
        'monitored_scheduled_task_log_items',
        'oauth_access_tokens',
        'oauth_auth_codes',
        'oauth_clients',
        'oauth_personal_access_clients',
        'oauth_refresh_tokens',
        'password_resets',
        'personal_access_tokens',
        'priv_ssh_keys',
        'rest_api_logs',
        'rest_api_tokens',
        'sessions',
        'snippet_task',
        'tag_task',
        'tracked_jobs',
        'user_roles',
        'role_permissions',
        'websockets_statistics_entries',
    ];

    /**
     * List every base table in the current database, regardless of driver.
     *
     * @return array<int, string>
     */
    public static function listAllBaseTables(): array
    {
        // schemaQualified=false returns bare table names (not "database.table"),
        // which keeps the result driver agnostic and comparable to EXCLUDED_TABLES.
        return Schema::getTableListing(schemaQualified: false);
    }

    public function listTables()
    {
        $filtered = array_values(array_diff(self::listAllBaseTables(), self::EXCLUDED_TABLES));

        return $this->successResponse('List of exportable tables.', ['tables' => $filtered]);
    }

    public function export($table)
    {
        $modelName = 'App\\Models\\' . Str::studly(Str::singular($table));
        $filename = $table . '.csv';

        if (Excel::store(new ModelExport($modelName), $filename, 'rconfig_exports')) {
            $logmsg = $filename . ' exported by ' . auth()->user()->username;
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'export');
            $downloadLink = '<a href="/download-export?filename=' . basename($filename) . '&type=export" class="card-pf-link-with-icon alink">  <span class="fa fa-arrow-circle-o-down"></span>Download File: ' . basename($filename) . '</a> ';
            $downloadUrl = '/download-export?filename=' . basename($filename) . '&type=export';

            return $this->successResponse('Exported ' . $table . ' to ' . $filename, ['downloadLink' => $downloadLink, 'downloadUrl' => $downloadUrl, 'filename' => $filename], 200);
        }

        return $this->failureResponse('Error exporting file');
    }
}
