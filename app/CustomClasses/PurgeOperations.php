<?php

namespace App\CustomClasses;

use App\Models\User;
use App\Notifications\DBPurgeOperationNotification;
use App\Notifications\MailPurgeOperationNotification;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Validator;

class PurgeOperations
{
    public function purge(Request $request)
    {
        Validator::make($request->all(), [
            'days' => 'required|integer|gt:0',
        ])->validate();

        switch ($request->purgetype) {
            case 'backup':
                $files = File::allFiles(storage_path('app/rconfig/backups'));
                break;
            case 'settings':
                $files = File::allFiles(storage_path() . '/logs');
                break;
        }

        if (! empty($files)) {
            $purgeSeconds = 86400 * $request->days;
            $limit = time() - $purgeSeconds;
            $purgelist = [];
            foreach ($files as $file) {
                if ($file->getMtime() < $limit) {
                    $purgelist[] = $file;
                }
            }

            File::delete($purgelist);

            $msg = $request->purgetype . ' files older than ' . $request->days . ' days are now purged.';
            $output = [
                'success' => true,
                'data' => '',
                'msg' => $msg,
            ];

            // send notification
            try {
                $username = Auth::user()->username;
                Notification::send(User::allUsersAndRecipients(), new MailPurgeOperationNotification($msg, $username));
                Notification::send(User::all(), new DBPurgeOperationNotification($msg, $username));

                $responseArray = ['success' => 200, 'msg' => $msg];
                activityLogIt(__CLASS__, __FUNCTION__, 'warn', $responseArray['msg'], 'purge');
            } catch (\Exception $e) {
                activityLogIt(__CLASS__, __FUNCTION__, 'error', $e->getMessage(), 'purge');
            }
        } else {
            // LOGGING
            $output = [
                'success' => false,
                'msg' => 'No files found!',
            ];
        }

        return $output;
    }
}
