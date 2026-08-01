<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Login username to be used by the controller.
     *
     * @var string
     */
    protected $username;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->username = $this->findUsername();
    }

    public function login(Request $request)
    {
        $user = null;

        if (! $user) {
            $msg = 'Authenticating user  (' . $request->username . ') against database.';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg, 'authentication');

            $this->validateLogin($request); // replaced $this->validateLogin in AuthenticatesUsers.php with private version in this class

            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }

            if ($this->attemptLogin($request)) {
                if ($user = Auth::user()) {
                    if ($this->isUnapprovedSocialiteUser($user)) {
                        return $this->rejectUnapprovedSocialiteUser($request, $user);
                    }

                    $msg = 'Local authentication for user ' . $user->email;
                    activityLogIt(__CLASS__, __FUNCTION__, 'info', $msg, 'authentication');

                    $user->last_login = Carbon::now();
                    $user->save();

                    return redirect('/dashboard');
                }

                return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);
            $msg = 'Local authentication failed.';
            activityLogIt(__CLASS__, __FUNCTION__, 'error', $msg, 'authentication');

            return $this->sendFailedLoginResponse($request);
        }
    }

    /**
     * An SSO provisioned account that an administrator has not approved yet.
     *
     * SocialiteController gates the SSO callback on is_socialite_approved, but
     * that gate did not cover the local login path. An unapproved SSO account
     * could set a password through the standard reset flow and sign in here,
     * skipping approval entirely. Local only accounts are unaffected, since the
     * check requires is_socialite.
     */
    private function isUnapprovedSocialiteUser(Authenticatable $user): bool
    {
        return (bool) $user->is_socialite && ! $user->is_socialite_approved;
    }

    /**
     * Tear down the session established by attemptLogin() and send the user
     * back to the login page with the same message the SSO callback uses.
     */
    private function rejectUnapprovedSocialiteUser(Request $request, Authenticatable $user): RedirectResponse
    {
        $msg = 'Your account is not approved to use rConfig yet. Please contact the administrator.';

        activityLogIt(__CLASS__, __FUNCTION__, 'error', 'Local authentication blocked for unapproved SSO user ' . $user->email, 'authentication');

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('message', $msg);
    }

    public function showLoginForm()
    {
        $banner = Banner::select('login_banner')->get();
        $login_banner = $banner[0]->login_banner;

        return view('auth.login', compact('login_banner'));
    }

    public function showLoggedOut()
    {
        // assume we're logged out at this point
        return view('auth.logged-out');
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->flush();
        $request->session()->regenerate();

        return redirect('/login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername()
    {
        $login = request()->input('username');

        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        request()->merge([$fieldType => $login]);

        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Validate the user login request.
     *
     * @return void
     *
     * @throws ValidationException
     */
    private function validateLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws ValidationException
     */
    private function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn(
            $this->throttleKey($request)
        );

        throw ValidationException::withMessages([
            'username' => [trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ])],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Get the failed login response instance.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages([
            'username' => [trans('auth.failed')],
        ]);
    }
}
