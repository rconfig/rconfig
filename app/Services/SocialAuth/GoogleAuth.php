<?php

namespace App\Services\SocialAuth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GoogleAuth
{
    public function register(Request $request)
    {

        $authedUser = SocialAuthHandler::checkErrors($request, 'google');

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
                    'microsoft_token' => $authedUser->token,
                    'microsoft_refresh_token' => $authedUser->refreshToken,
                    'email_verified_at' => now(),
                    'is_socialite' => true,
                ]), function (User $user) {
                    // $this->createTeam($user);
                });
            } catch (\Throwable $th) {
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
