<?php

namespace Tests\Fasttests\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Regression tests for unauthenticated self-registration granting Admin.
 *
 * Reported 2026-08-01 against core-8.2.9. A bare `Auth::routes()` added by the
 * SSO work in 5ae01b37 sat below the deliberate `Auth::routes(['register' =>
 * false])` from dc7d6285. Laravel does not deduplicate or retract routes, so
 * `register` came back. The stock RegistersUsers scaffold never set a role, the
 * users.role column defaulted to 'Admin', and User::$guarded is empty, so a
 * single anonymous POST /register produced a logged in Administrator.
 *
 * These tests cover every link in that chain independently, so breaking any one
 * of them fails the build rather than silently re-opening the hole.
 */
class RegistrationDisabledTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->beginTransaction();
    }

    /**
     * The route itself must not exist. This is the link that actually broke.
     */
    public function test_no_route_is_named_register(): void
    {
        $names = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route): ?string => $route->getName())
            ->filter()
            ->values()
            ->all();

        $this->assertNotContains('register', $names, 'A route named `register` is registered. Self-service registration is re-enabled.');
    }

    /**
     * No route of any verb may resolve to a registration handler.
     */
    public function test_no_route_points_at_a_registration_controller(): void
    {
        $actions = collect(Route::getRoutes()->getRoutes())
            ->map(fn ($route): string => (string) ($route->getActionName() ?? ''))
            ->filter(fn (string $action): bool => str_contains($action, 'RegisterController'))
            ->values()
            ->all();

        $this->assertSame([], $actions, 'A route still resolves to a registration controller.');
    }

    /**
     * A guest hitting the registration form must not receive one. The SPA
     * catch-all currently redirects to login, which is a valid disabled state.
     * What matters is that no 2xx registration page is served.
     */
    public function test_get_register_does_not_serve_a_registration_form(): void
    {
        $response = $this->get('/register');

        $this->assertFalse(
            $response->isSuccessful(),
            'GET /register returned a successful response, so a registration form may be reachable.'
        );
    }

    /**
     * The actual exploit request. It must not create an account and must not
     * authenticate the caller.
     */
    public function test_post_register_does_not_create_an_account_or_log_anyone_in(): void
    {
        $email = 'regression-selfregister@example.test';

        $response = $this->postJson('/register', [
            'name' => 'Regression Check',
            'email' => $email,
            'password' => 'RegressionCheck123!',
            'password_confirmation' => 'RegressionCheck123!',
        ]);

        $this->assertFalse(
            $response->isSuccessful(),
            'POST /register returned a successful response. Self-registration is live.'
        );

        $this->assertNull(
            User::where('email', $email)->first(),
            'An unauthenticated POST /register created a user account.'
        );

        $this->assertGuest();
    }

    /**
     * Guards the exact regression shape: a second, bare Auth::routes() call
     * anywhere in the route files silently re-enables `register`.
     */
    public function test_route_files_contain_exactly_one_auth_routes_call_and_it_disables_register(): void
    {
        $calls = [];

        foreach (glob(base_path('routes/*.php')) ?: [] as $file) {
            $contents = file_get_contents($file) ?: '';

            if (preg_match_all('/Auth::routes\((.*?)\);/s', $contents, $matches)) {
                foreach ($matches[1] as $arguments) {
                    $calls[] = basename($file) . ': Auth::routes(' . trim($arguments) . ')';
                }
            }
        }

        $this->assertCount(
            1,
            $calls,
            "Expected exactly one Auth::routes() call across the route files. Found:\n" . implode("\n", $calls)
        );

        $this->assertStringContainsString(
            "'register' => false",
            $calls[0],
            'The Auth::routes() call must explicitly disable registration.'
        );
    }

    /**
     * Defense in depth. Even if a registration path returns, a user row created
     * without an explicit role must not come out as an Administrator.
     */
    public function test_a_user_created_without_an_explicit_role_is_not_an_admin(): void
    {
        $user = User::create([
            'name' => 'No Role Specified',
            'email' => 'no-role-specified@example.test',
            'password' => bcrypt('NoRoleSpecified123!'),
        ]);

        $this->assertNotSame(
            'Admin',
            $user->fresh()->role,
            'A user created without an explicit role defaulted to Admin. The users.role column default is fail-open.'
        );
    }

    /**
     * Assert the schema default directly, so the guarantee above cannot be
     * quietly undone by a future migration.
     */
    public function test_the_users_role_column_does_not_default_to_admin(): void
    {
        $default = DB::selectOne(
            'SELECT COLUMN_DEFAULT AS `default` FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['users', 'role']
        );

        $this->assertNotNull($default, 'Could not read the users.role column default.');
        $this->assertNotSame(
            'Admin',
            trim((string) $default->default, "'"),
            'The users.role column still defaults to Admin.'
        );
    }
}
