<?php

namespace App\Services\SocialAuth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthHandler
{
    public static function checkErrors(Request $request, $driver)
    {
        if (! $request->has('code')) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Authorization code is missing. Please try again.', 'auth');

            return redirect()->to('/login')
                ->with('message', 'Authorization code is missing. Please try again.');
        }

        if ($request->has('denied')) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Access was denied. Please try again.', 'auth');

            return redirect()->route('login')
                ->with('message', 'Access was denied. Please try again.');
        }

        try {
            $authedUser = Socialite::driver($driver)->user();
        } catch (\Exception $e) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Unable to authenticate using Microsoft. Please try again. Error: ' . $e->getMessage(), 'auth');

            return redirect()->route('login')
                ->with('message', 'Unable to authenticate using Microsoft. Please try again. Error: ' . $e->getMessage());
        }

        if (! $authedUser) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Your account is not registered.', 'auth');

            return redirect()->route('login')
                ->with('message', 'Your account is not registered.');
        }

        return $authedUser;
    }
}
