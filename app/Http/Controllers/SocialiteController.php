<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SocialAuth\GoogleAuth;
use App\Services\SocialAuth\MicrosoftAuth;
use App\Services\SocialAuth\OktaAuth;
use App\Services\SocialAuth\Saml2Auth;
use App\Services\UserLog\UserLogActivity;
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

        return Socialite::driver($provider)->setHttpClient(new \GuzzleHttp\Client(['verify' => false]))->redirect();
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

                if (! $user instanceof User) {
                    return redirect()->to('/login')
                        ->with('message', 'SAML2 authentication failed. Please contact your administrator.');
                }
                break;
            case 'google':
                $user = (new GoogleAuth)->register($request);

                if (! $user instanceof User) {
                    return redirect()->to('/login')
                        ->with('message', 'Google authentication failed. Please contact your administrator.');
                }
                break;
            case 'okta':
                $user = (new OktaAuth)->register($request);

                if (! $user instanceof User) {
                    return redirect()->to('/login')
                        ->with('message', 'Okta authentication failed. Please contact your administrator.');
                }
                break;
            case 'microsoft':
                $user = (new MicrosoftAuth)->register($request);

                if (! $user instanceof User) {
                    return redirect()->to('/login')
                        ->with('message', 'Microsoft authentication failed. Please contact your administrator.');
                }
                break;
            default:
                activityLogIt(__CLASS__, __FUNCTION__, 'error', "Unsupported authentication provider: {$provider}", 'auth');

                return redirect()->to('/login')
                    ->with('message', 'Authentication provider is not supported.');
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
