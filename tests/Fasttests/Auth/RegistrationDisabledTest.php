<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->beginTransaction();
});

test('no route is named register', function () {
    $names = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route): ?string => $route->getName())
        ->filter()
        ->values()
        ->all();

    // Left as a PHPUnit-style assertion: Pest's toContain() has no message parameter,
    // so converting would silently drop the diagnostic text below.
    $this->assertNotContains('register', $names, 'A route named `register` is registered. Self-service registration is re-enabled.');
});

test('no route points at a registration controller', function () {
    $actions = collect(Route::getRoutes()->getRoutes())
        ->map(fn ($route): string => (string) ($route->getActionName() ?? ''))
        ->filter(fn (string $action): bool => str_contains($action, 'RegisterController'))
        ->values()
        ->all();

    expect($actions)->toBe([], 'A route still resolves to a registration controller.');
});

test('get register does not serve a registration form', function () {
    $response = $this->get('/register');

    expect($response->isSuccessful())->toBeFalse('GET /register returned a successful response, so a registration form may be reachable.');
});

test('post register does not create an account or log anyone in', function () {
    $email = 'regression-selfregister@example.test';

    $response = $this->postJson('/register', [
        'name' => 'Regression Check',
        'email' => $email,
        'password' => 'RegressionCheck123!',
        'password_confirmation' => 'RegressionCheck123!',
    ]);

    expect($response->isSuccessful())->toBeFalse('POST /register returned a successful response. Self-registration is live.');

    expect(User::where('email', $email)->first())->toBeNull('An unauthenticated POST /register created a user account.');

    $this->assertGuest();
});

test('route files contain exactly one auth routes call and it disables register', function () {
    $calls = [];

    foreach (glob(base_path('routes/*.php')) ?: [] as $file) {
        $contents = file_get_contents($file) ?: '';

        if (preg_match_all('/Auth::routes\((.*?)\);/s', $contents, $matches)) {
            foreach ($matches[1] as $arguments) {
                $calls[] = basename($file) . ': Auth::routes(' . trim($arguments) . ')';
            }
        }
    }

    expect($calls)->toHaveCount(1, "Expected exactly one Auth::routes() call across the route files. Found:\n" . implode("\n", $calls));

    $this->assertStringContainsString(
        "'register' => false",
        $calls[0],
        'The Auth::routes() call must explicitly disable registration.'
    );
});

test('a user created without an explicit role is not an admin', function () {
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
});

test('the users role column does not default to admin', function () {
    $default = DB::selectOne(
        'SELECT COLUMN_DEFAULT AS `default` FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
        ['users', 'role']
    );

    expect($default)->not->toBeNull('Could not read the users.role column default.');
    $this->assertNotSame(
        'Admin',
        trim((string) $default->default, "'"),
        'The users.role column still defaults to Admin.'
    );
});
