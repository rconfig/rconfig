<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreSettingsEmailRequest;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\TestMailNotification;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class SettingEmailController extends ApiBaseController
{
    use RespondsWithHttpStatus;

    public function __construct(Setting $model, $modelname = 'setting')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }
 
    public function show($id, $relationship = null, $withCount = null)
    {
        return parent::show($id);
    }
 
    public function update($id, StoreSettingsEmailRequest $request)
    {
        parent::updateResource($id, $request->toDTO()->toArray(), 1);

        if (!App()->environment('testing')) {
            Artisan::call('config:cache'); // cannot to a config:cache when testing
        }

        return $this->successResponse(Str::ucfirst($this->modelname) . ' edited successfully!');
    }

    public function TestMailNotifications()
    {
        try {
            $users = $this->makeRecipients();
            foreach ($users as $user) {
                Notification::send($user, new TestMailNotification());
            }

            return $this->successResponse('Test notification sent successfully!');
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'Test Notifications');

            return $this->failureResponse($exception->getMessage());
        }
    }

    public function TestMail()
    {
        try {
            $users = $this->makeRecipients();
            foreach ($users as $user) {
                Mail::send('emails.test-mail', [], function ($message) use ($user) {
                    $message->to($user->email)->subject('rConfig Test Mail');
                });
            }

            return $this->successResponse('Email settings tested successfully, please check your email for the test message!');
        } catch (\Exception $exception) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $exception->getMessage(), 'Test Mail');

            return $this->failureResponse($exception->getMessage());
        }
    }

    private function makeRecipients()
    {
        if (!$this->show(1)->mail_to_email) {
            return $this->failureResponse('Invalid recipient email address');
        }

        $recipientsArr = explode(';', trim($this->show(1)->mail_to_email, ';'));

        foreach ($recipientsArr as $recipient) {
            $users[] = User::make(['email' => trim($recipient)]);
        }

        return $users;
    }
}
