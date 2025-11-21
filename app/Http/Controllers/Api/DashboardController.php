<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\ConfigSummary;
use App\Models\Device;
use App\Models\Task;
use App\Services\Utilities\ServerTimeService;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    use RespondsWithHttpStatus;

    public function getLast5Devices()
    {
        $result = Device::select('id', 'device_name', 'device_ip', 'created_at', 'status', 'last_seen')->orderBy('created_at', 'desc')->take(5)->get();

        return $this->successResponse('Success', $result);
    }

    public function getLast5unreachableDevices()
    {
        $result = Device::select('id', 'device_name', 'device_ip', 'created_at', 'status', 'last_seen')->orderBy('last_seen', 'desc')->where('status', '!=', '1')->where('status', '!=', '100')->orWhereNull('status')->take(5)->get();

        return $this->successResponse('Success', $result);
    }

    public function getQueueInfo()
    {
        $result['q_total_count'] = DB::table('job_batches')->count();
        $result['q_failed_count'] = DB::table('job_batches')->where('failed_jobs', '>', '0')->count();
        $result['q_last_job'] = DB::table('job_batches')->latest('created_at')->first();

        return $this->successResponse('Success', $result);
    }

    public function getSysInfo()
    {

        // get param from url
        $clearcache = request()->input('clearcache');

        if ($clearcache == 'true') {
            Cache::forget('dashboard.sysinfo');
        }

        return Cache::remember('dashboard.sysinfo', 604800, function () {
            return $this->Sysinfo();
        });
    }

    public function getConfigInfo()
    {
        $deviceCount = Device::count();
        $deviceDownCount = Device::whereIn('status', [Device::STATUS_UNREACHABLE, Device::STATUS_UNKNOWN])->count();
        $configFileTotalCount = ConfigSummary::sum('total_file_count');
        $configTotalCount = ConfigSummary::sum('total_count');
        $failedConfigCount = ConfigSummary::sum('download_status_0_count');
        $lastConfig = Config::where('download_status', '!=', 0)->orderBy('id', 'desc')->first();

        $data = [
            'deviceCount' => $deviceCount,
            'deviceDownCount' => $deviceDownCount,
            'configFileTotalCount' => $configFileTotalCount,
            'configTotalCount' => $configTotalCount,
            'failedConfigCount' => $failedConfigCount,
            'lastConfig' => $lastConfig ? $lastConfig : null,
        ];

        return $this->successResponse('Success', $data);
    }

    public function serverTime()
    {
        $serverTimeService = new ServerTimeService;
        $serverTime = $serverTimeService->getServerTime();

        return $this->successResponse('Success', $serverTime);
    }

    private function Sysinfo()
    {
        $this->checkAppUrl();
        $osInfo = getOSInformation();
        $osVersion = isset($osInfo['PRETTY_NAME']) ? $osInfo['PRETTY_NAME'] : $osInfo['PRETTY_NAME'];
        $osVersion = str_replace('(Core)', '', $osVersion);
        $osVersion = str_replace('Linux', '', $osVersion);
        $serverInfos = []; // array for Server Info Table
        $serverInfos['OSVersion'] = $osVersion;
        $serverInfos['localIp'] = gethostbyname(gethostname());
        try {
            $serverInfos['PublicIP'] = Http::timeout(3)->get('https://ipinfo.io/json')->json()['ip'];
        } catch (\Exception $e) {
            $serverInfos['PublicIP'] = '0.0.0.0';
        }
        $serverInfos['ServerName'] = gethostname();
        $serverInfos['PHPVersion'] = phpversion() . ' / ' . app()->version();
        $serverInfos['RedisVersion'] = $this->redisVersion();

        if (config('database.default') == 'mysql' || config('database.default') == 'test_mysql') {
            $serverInfos['MySQLVersion'] = 'MySQL ' . $this->mysqlversion();
        }
        if (strpos(config('database.default'), 'pgsql') !== false) {
            $serverInfos['MySQLVersion'] = 'PostgreSQL ' . $this->mysqlversion();
        }
        // get system timezone
        $serverInfos['timezone'] = config('app.timezone');

        $serverInfos['url'] = config('app.url');
        $serverInfos['systemUptime'] = $this->getSystemUptime();

        return $serverInfos;
    }

    private function mysqlversion()
    {

        if (config('database.default') == 'mysql' || config('database.default') == 'test_mysql') {
            $mysqlVersionNumber = DB::select(DB::raw('SHOW VARIABLES LIKE "%version%"')->getValue(DB::connection()->getQueryGrammar()));

            return $mysqlVersionNumber[7]->Value;
        }

        if (strpos(config('database.default'), 'pgsql') !== false) {
            // $mysqlVersionNumber = DB::select(DB::raw('SHOW VARIABLES LIKE "%version%"')->getValue(DB::connection()->getQueryGrammar()));
            $mysqlVersionNumber = DB::select(DB::raw('SHOW server_version')->getValue(DB::connection()->getQueryGrammar()));

            return $mysqlVersionNumber[0]->server_version;
        }
    }

    private function redisVersion()
    {
        $redis_info = Redis::info();

        return $redis_info['Server']['redis_version'];
    }

    private function checkAppUrl()
    {
        if (config('app.url') === '') {
            if ($this->checkOrSetAppUrlNotification()) {
                return true;
            }
        }
    }

    private function getSystemUptime()
    {
        $uptime = shell_exec('uptime -p');
        if ($uptime) {
            return trim($uptime);
        }

        return 'Uptime information not available';
    }
}
