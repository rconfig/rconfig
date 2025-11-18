<?php

namespace App\Services\SocialAuth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MicrosoftAuth
{
    // https://dev.to/judicaelg/laravel-socialite-configure-microsoft-azure-16hj
    public function register(Request $request)
    {
        $authedUser = SocialAuthHandler::checkErrors($request, 'microsoft');

        if ($authedUser instanceof \Illuminate\Http\RedirectResponse) {
            // checks if the user is an instance of RedirectResponse meaning there was an error in the SocialAuthHandler method
            return $authedUser;
        }

        return DB::transaction(function () use ($authedUser) {

            try {
                return tap(User::updateOrCreate([
                    'microsoft_id' => $authedUser->id,
                ], [
                    'name' => $authedUser->name,
                    'email' => $authedUser->email,
                    'password' => bcrypt(str()->random(16)), // random password
                    'microsoft_token' => $authedUser->token,
                    'microsoft_refresh_token' => $authedUser->refreshToken,
                    'email_verified_at' => now(),
                    'is_socialite' => true,
                ]), function (User $authedUser) {
                    // $this->createTeam($authedUser);
                });
            } catch (\Throwable $th) {
                activityLogIt(__CLASS__, __FUNCTION__, 'error', 'An error occurred while updating your information. Please contact support. Error: ' . $th->getMessage(), 'auth');

                if ($th->getCode() == 23000) {
                    User::where('email', $authedUser->email)->update([
                        'microsoft_id' => $authedUser->id,
                        'microsoft_token' => $authedUser->token,
                        'microsoft_refresh_token' => $authedUser->refreshToken,
                    ]);

                    return User::where('email', $authedUser->email)->first();
                }

                return redirect()->route('login')
                    ->with('message', 'An error occurred while updating your information. Please contact support. Error: ' . $th->getMessage());
            }
        });
    }
}
