<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SocialAuth\GoogleAuth;
use App\Services\SocialAuth\MicrosoftAuth;
use App\Services\SocialAuth\OktaAuth;
use App\Services\SocialAuth\Saml2Auth;
use App\Services\UserLog\UserLogActivity;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController
{
    protected $supportedProviders = ['microsoft', 'okta', 'google', 'saml2'];

    public function redirect($provider)
    {

        if (! in_array($provider, $this->supportedProviders)) {
            return redirect()->to('/login')
                ->with('message', 'The socialite provider is not supported yet, or is blank.');
        }

        return Socialite::driver($provider)->setHttpClient(new Client(['verify' => false]))->redirect();
    }

    public function callback($provider, Request $request)
    {

        if (! in_array($provider, $this->supportedProviders)) {
            return redirect()->to('/login')
                ->with('message', 'The socialite provider is not supported yet, or is blank.');
        }
        switch ($provider) {
            case 'saml2':
                $user = (new Saml2Auth)->register($request);
                break;
            case 'google':
                $user = (new GoogleAuth)->register($request);
                break;
            case 'okta':
                $user = (new OktaAuth)->register($request);
                break;
            case 'microsoft':
                $user = (new MicrosoftAuth)->register($request);
                break;
            default:
                activityLogIt(__CLASS__, __FUNCTION__, 'error', "Unsupported authentication provider: {$provider}", 'auth');

                return redirect()->to('/login')
                    ->with('message', 'Authentication provider is not supported.');
        }

        // register() returns either the authenticated User or a RedirectResponse
        // carrying a specific error message set by SocialAuthHandler (missing
        // code/SAMLResponse, denied, provider failure, account not registered,
        // etc.). Return that response as-is so the specific message reaches the
        // user, rather than discarding it in favor of a generic fallback.
        if (! $user instanceof User) {
            return $user;
        }

        try {
            if (! $user->is_socialite_approved) {
                $msg = 'Your ' . $provider . ' account is not approved to use rConfig yet. Please contact the administrator.';
                UserLogActivity::addToLog($user->name . ': ' . $msg);

                return redirect('/login')->with('message', $msg);
            }

            Auth::login($user);
        } catch (\Throwable $th) {
            activityLogIt(__CLASS__, __FUNCTION__, 'error', 'An error occurred while logging you in. Please contact support. Error: ' . $th->getMessage(), 'auth');
            Log::error($th->getMessage());
            Auth::logout();

            return redirect('/login')->with('message', 'You have been logged out. Ask your admin to check the logs.');
        }

        // Check for intended URL from session (after timeout)
        $intendedUrl = $request->session()->get('url.intended', '/dashboard');

        return redirect()->to($intendedUrl);
    }
}
