<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Command;
use App\Models\Config;
use App\Models\Device;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserOnboardingController extends Controller
{
    public function getSteps(Request $request)
    {
        $this->updateSteps();
        return response()->json(auth()->user()->onboarding_status);
    }


    private function updateSteps()
    {
        $steps = $this->steps();

        $steps['create_new_user']['status'] = User::where('email', '!=', 'admin@domain.com')->count() > 0;
        $steps['add_command']['status'] = Command::whereNotIn('command', ['show clock', 'show version', 'show run'])->count() > 0;
        $steps['add_device']['status'] = Device::count() > 0;
        $steps['add_schedule_task']['status'] =  Task::count() > 0;
        $steps['view_configs']['status'] = Config::count() > 0;
        $steps['setup_email']['status'] = !empty(config('mail.mailers.smtp.username'));

        $user = auth()->user();
        $user->onboarding_status = $steps;
        $user->save();
    }

    private function steps()
    {
        return [
            'create_new_user' => [
                'name' => 'Create New User',
                'status' => false,
                'link' => '/users/create'
            ],
            'add_command' => [
                'name' => 'Add Commands',
                'status' => false,
                'link' => '/commands/create'
            ],
            'add_device' => [
                'name' => 'Add Device',
                'status' => false,
                'link' => '/devices/create'
            ],
            'add_schedule_task' => [
                'name' => 'Add Schedule Task',
                'status' => false,
                'link' => '/tasks/create'
            ],
            'view_configs' => [
                'name' => 'View Configs',
                'status' => false,
                'link' => '/configs'
            ],
            'setup_email' => [
                'name' => 'Setup Email',
                'status' => false,
                'link' => '/settings/email'
            ],
        ];
    }
}
