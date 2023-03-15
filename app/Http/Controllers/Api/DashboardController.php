<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\Task;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    use RespondsWithHttpStatus;

    public function getSysInfo()
    {
        return Cache::remember('dashboard.sysinfo', 604800, function () {
            return $this->Sysinfo();
        });
    }

    public function getConfigInfo()
    {
        $deviceCount = DB::table('devices')->count();
        $deviceDownCount = DB::table('devices')->where('status', 0)->count();
        $configTotalCount = DB::table('configs')->count();
        $configDownCount = DB::table('configs')->where('download_status', 0)->count();

        $data = [
            'deviceCount' => $deviceCount,
            'deviceDownCount' => $deviceDownCount,
            'configTotalCount' => $configTotalCount,
            'configDownCount' => $configDownCount,
        ];

        return $this->successResponse('Success', $data);
    }

    private function Sysinfo()
    {
        $this->checkAppUrl();

        $osVersion = getOSInformation()['pretty_name'];
        $osVersion = str_replace('(Core)', '', $osVersion);
        $osVersion = str_replace('Linux', '', $osVersion);

        $serverInfos = []; // array for Server Info Table
        $serverInfos['OSVersion'] = $osVersion;
        $serverInfos['localIp'] = gethostbyname(gethostname());
        $serverInfos['PublicIP'] = trim(shell_exec('dig +short myip.opendns.com @resolver1.opendns.com'));
        $serverInfos['ServerName'] = gethostname();
        $serverInfos['PHPVersion'] = phpversion() . ' / ' . app()->version();
        $serverInfos['RedisVersion'] = $this->redisVersion();
        $serverInfos['MySQLVersion'] = $this->mysqlversion();

        return $serverInfos;
    }

    private function getBatchJobData()
    {
        $result['q_total_count'] = DB::table('job_batches')->count();
        $result['q_failed_count'] = DB::table('job_batches')->where('failed_jobs', '>=', '0')->count();
        $result['q_last_job'] = DB::table('job_batches')->latest('created_at')->first();

        return $result;
    }

    private function mysqlversion()
    {

        $mysqlVersionNumber = DB::select(DB::raw('SHOW VARIABLES LIKE "%version%"')->getValue(DB::connection()->getQueryGrammar()));

        return $mysqlVersionNumber[1]->Value;
    }

    private function redisVersion()
    {
        $redis_info = Redis::info();

        return $redis_info['Server']['redis_version'];
    }

    private function systemUptime()
    {
        // uptime
        $get_uptime = file_get_contents('/proc/uptime');
        $uptime = explode(' ', $get_uptime);
        $uptime_days = floor($uptime[0] / 86400);
        $uptime_hours = floor(($uptime[0] / 3600) % 24);
        $uptime_minutes = floor(($uptime[0] / 60) % 60);
        $uptime_seconds = ($uptime[0] % 60);

        return $uptime_days . ' Days ' . $uptime_hours . ':' . $uptime_minutes;
    }

    private function memInfo()
    {
        // mem usage
        $get_meminfo = file('/proc/meminfo');
        $meminfo_total = filter_var($get_meminfo[0], FILTER_SANITIZE_NUMBER_INT);
        $meminfo_cached = filter_var($get_meminfo[2], FILTER_SANITIZE_NUMBER_INT);
        $meminfo_free = filter_var($get_meminfo[1], FILTER_SANITIZE_NUMBER_INT);
        if ($meminfo_total >= 10485760) {
            $mem_total = round(($meminfo_total / 1048576), 0) . ' MB';
            $mem_cached = round(($meminfo_cached / 1048576), 0) . ' MB';
            $mem_free = round((($meminfo_free + $meminfo_cached) / 1048576), 0) . ' MB';
            $mem_multiple = 'GB';
        } else {
            $mem_total = round(($meminfo_total / 1024), 0) . ' MB';
            $mem_cached = round(($meminfo_cached / 1024), 0) . ' MB';
            $mem_free = round((($meminfo_free + $meminfo_cached) / 1024), 0) . ' MB';
            $mem_multiple = 'MB';
        }
        $mem = [
            'total' => $mem_total,
            // 'Memory cached' => $mem_cached,
            'free' => $mem_free,
        ];

        return $mem;
    }

    private function cpuLoad()
    {
        // cpu load
        $loads = sys_getloadavg();
        $core_nums = trim(shell_exec("grep -P '^processor' /proc/cpuinfo|wc -l"));
        $load = round($loads[0] / ($core_nums + 1) * 100, 2);

        return $load;
    }

    private function diskInfo()
    {
        /* get disk space free (in bytes) */
        $df = disk_free_space('/');
        /* and get disk space total (in bytes)  */
        $dt = disk_total_space('/');
        /* now we calculate the disk space used (in bytes) */
        // $du = $dt - $df;
        /* percentage of disk used - this will be used to also set the width % of the progress bar */
        // $dp = sprintf('%.2f',($du / $dt) * 100);

        /* and we formate the size from bytes to MB, GB, etc. */

        $df = formatSize($df);
        // $du = formatSize($du);
        $dt = formatSize($dt);

        $disk = [
            'free' => $df,
            // 'Disk Used Space' => $du,
            'total' => $dt,
            // 'Disk Percent Used' => $dp . '%'
        ];

        return $disk;
    }

    private function checkAppUrl()
    {
        if (config('app.url') === '') {
            if ($this->checkOrSetAppUrlNotification()) {
                return true;
            }
        }
    }
}
