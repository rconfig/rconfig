<?php

use App\Casts\EncryptStringCast;
use App\Exports\ModelExport;
use App\Http\Controllers\Api\ExportController;
use App\Models\Device;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->beginTransaction();
    Storage::fake('rconfig_exports');

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

afterEach(function () {
    $this->rollBackTransaction();
});

/**
 * @return list<string|null>
 */
function exportCsvHeadings(string $csv): array
{
    return str_getcsv(strtok($csv, "\n") ?: '', ',', '"', '\\');
}

/**
 * @return array<class-string<Model>, list<string>>
 */
function exportModelsWithEncryptedCasts(): array
{
    $modelsWithEncryptedCasts = [];

    foreach (File::allFiles(app_path('Models')) as $modelFile) {
        $modelClass = 'App\\Models\\' . str_replace(['/', '.php'], ['\\', ''], $modelFile->getRelativePathname());

        if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class) || (new ReflectionClass($modelClass))->isAbstract()) {
            continue;
        }

        $encryptedAttributes = array_keys(array_filter(
            (new $modelClass)->getCasts(),
            fn (string $cast): bool => $cast === EncryptStringCast::class || str_starts_with($cast, 'encrypted')
        ));

        if ($encryptedAttributes !== []) {
            $modelsWithEncryptedCasts[$modelClass] = $encryptedAttributes;
        }
    }

    return $modelsWithEncryptedCasts;
}

test('it can get the list of exportable tables', function () {
    $response = $this->get('/api/settings/export/list-tables');
    $response->assertStatus(200);
    expect($response->json()['data'])->toHaveKey('tables');
    expect($response->json()['data']['tables'])->toContain('activity_log');
    expect($response->json()['data']['tables'])->toContain('devices');
    expect($response->json()['data']['tables'])->toContain('users');
    expect($response->json()['data']['tables'])->not->toContain('migrations');
    expect($response->json()['data']['tables'])->not->toContain('sessions');
    expect($response->json()['data']['tables'])->not->toContain('jobs');

    // check that the list of tables matches the expected list based on the excluded tables
    $expected = array_values(array_diff(
        ExportController::listAllBaseTables(),
        ExportController::EXCLUDED_TABLES
    ));
    $response->assertJsonCount(count($expected), 'data.tables');
    expect($response->json('data.tables'))->toEqualCanonicalizing($expected);
});

test('it can export a table', function () {
    // https://docs.laravel-excel.com/3.1/exports/testing.html#testing-downloads
    $table = 'users';
    User::factory()->create(['username' => 'export-listed-user']);

    $response = $this->get('/api/settings/export/get-table/' . $table)
        ->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'downloadLink',
            'downloadUrl',
            'filename',
        ],
    ]);
    $response->assertJsonFragment([
        'downloadUrl' => '/download-export?filename=users.csv&type=export',
        'filename' => 'users.csv',
    ]);

    Storage::disk('rconfig_exports')->assertExists('users.csv');
    $csv = (string) Storage::disk('rconfig_exports')->get('users.csv');
    $headings = exportCsvHeadings($csv);
    expect($headings)->toContain('username');
    expect($headings)->not->toContain('password');
    expect($headings)->not->toContain('remember_token');
    expect($csv)->toContain('export-listed-user');
});

test('an excluded table cannot be exported', function (string $table) {
    $this->get('/api/settings/export/get-table/' . $table)->assertNotFound();

    expect(Storage::disk('rconfig_exports')->allFiles())->toBe([]);
})->with(fn (): array => ExportController::EXCLUDED_TABLES);

test('a table absent from the export list cannot be exported', function (string $table) {
    $this->get('/api/settings/export/list-tables')
        ->assertJsonPath('data.tables', fn (array $tables): bool => ! in_array($table, $tables, true));

    $this->get('/api/settings/export/get-table/' . $table)->assertNotFound();

    expect(Storage::disk('rconfig_exports')->allFiles())->toBe([]);
})->with([
    'singular alias of an exportable table' => ['user'],
    'singular alias of an excluded table' => ['rest_api_token'],
    'table that does not exist' => ['no_such_table'],
]);

test('the devices export contains no device passwords', function () {
    Device::factory()->create([
        'device_password' => 'export-sentinel-device-password',
        'device_enable_password' => 'export-sentinel-enable-password',
    ]);

    $this->get('/api/settings/export/get-table/devices')->assertOk();

    $csv = (string) Storage::disk('rconfig_exports')->get('devices.csv');
    $headings = exportCsvHeadings($csv);
    expect($headings)->toContain('device_name');
    expect($headings)->toContain('tags');
    expect($headings)->not->toContain('device_password');
    expect($headings)->not->toContain('device_enable_password');
    expect($headings)->not->toContain('view_url');
    expect($csv)->not->toContain('export-sentinel-device-password');
    expect($csv)->not->toContain('export-sentinel-enable-password');
});

test('every encrypted cast attribute is omitted from its model export', function () {
    $encryptedModels = exportModelsWithEncryptedCasts();
    expect($encryptedModels)->not->toBeEmpty();

    foreach ($encryptedModels as $modelClass => $encryptedAttributes) {
        $row = $modelClass::query()->first()
            ?? (method_exists($modelClass, 'factory') ? $modelClass::factory()->create() : null);
        expect($row)->not->toBeNull($modelClass . ' needs a seeded row for this test to prove anything');

        $sentinels = collect($encryptedAttributes)
            ->filter(fn (string $attribute): bool => Schema::hasColumn($row->getTable(), $attribute))
            ->mapWithKeys(fn (string $attribute): array => [$attribute => 'export-sentinel-' . Str::random(12)])
            ->all();
        $row->forceFill($sentinels)->saveQuietly();

        $filename = Str::snake(class_basename($modelClass)) . '.csv';
        Excel::store(new ModelExport($modelClass), $filename, 'rconfig_exports');
        $csv = (string) Storage::disk('rconfig_exports')->get($filename);
        $headings = exportCsvHeadings($csv);

        foreach ($encryptedAttributes as $attribute) {
            $this->assertNotContains($attribute, $headings, $modelClass . ' export has the ' . $attribute . ' column');
        }

        foreach ($sentinels as $attribute => $sentinel) {
            $this->assertStringNotContainsString($sentinel, $csv, $modelClass . ' export has the decrypted ' . $attribute . ' value');
        }
    }
});
