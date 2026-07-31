<?php

namespace App\Services\SocialAuth;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthHandler
{
    public static function checkErrors(Request $request, $driver)
    {
        if ($driver === 'saml2') {
            // SAML2 responses arrive as a POSTed SAMLResponse (or SAMLart), not an
            // OAuth2 'code' query param, so the OAuth-style check below doesn't apply here.
            if (! $request->has('SAMLResponse') && ! $request->has('SAMLart')) {
                activityLogIt(__CLASS__, __FUNCTION__, 'error', 'SAML response is missing. Please try again.', 'auth');

                return redirect()->to('/login')
                    ->with('message', 'SAML response is missing. Please try again.');
            }
        } elseif (! $request->has('code')) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Authorization code is missing. Please try again.', 'auth');

            return redirect()->to('/login')
                ->with('message', 'Authorization code is missing. Please try again.');
        }

        // SAML2 has no OAuth-style 'denied' query param -- denial/errors are
        // encoded inside the SAMLResponse body itself, so this check only
        // applies to OAuth-style drivers.
        if ($driver !== 'saml2' && $request->has('denied')) {
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
