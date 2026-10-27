<?php

use App\Exports\DeviceImportTemplateExport;
use App\Exports\DeviceInventoryExport;
use App\Imports\DevicesImport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Real writes and reads through laravel-excel 4 and PhpSpreadsheet 5, no Excel::fake(),
 * so a breaking change in either library shows up here rather than in a user's download.
 */
function writeSpreadsheet(object $export, string $extension): string
{
    $writerType = $extension === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX;
    $path = sys_get_temp_dir() . '/rconfig_excel_round_trip_' . uniqid() . '.' . $extension;
    file_put_contents($path, Excel::raw($export, $writerType));

    return $path;
}

/**
 * @return list<list<mixed>>
 */
function readSpreadsheet(string $path): array
{
    return IOFactory::load($path)->getActiveSheet()->toArray();
}

afterEach(function () {
    foreach (glob(sys_get_temp_dir() . '/rconfig_excel_round_trip_*') as $file) {
        @unlink($file);
    }
});

test('device inventory export writes the union of row keys as headings', function (string $extension) {
    $export = new DeviceInventoryExport([
        ['device_name' => 'r1', 'device_ip' => '192.0.2.1'],
        ['device_name' => 'r2', 'vendor' => 'Cisco'],
    ]);

    $rows = readSpreadsheet(writeSpreadsheet($export, $extension));

    expect($rows[0])->toBe(['device_name', 'device_ip', 'vendor']);
    expect($rows[1])->toBe(['r1', '192.0.2.1', null]);
    expect($rows[2])->toBe(['r2', null, 'Cisco']);
})->with(['xlsx', 'csv']);

test('device import template writes only its heading row to xlsx', function () {
    $rows = readSpreadsheet(writeSpreadsheet(new DeviceImportTemplateExport, 'xlsx'));

    expect($rows)->toBe([DeviceImportTemplateExport::COLUMNS]);
});

test('devices import reads an xlsx file keyed by its heading row', function () {
    $source = new class implements FromArray
    {
        public function array(): array
        {
            return [
                ['Device Name', 'Device IP'],
                ['core-sw-01', '192.0.2.10'],
            ];
        }
    };

    $rows = Excel::toCollection(new DevicesImport, writeSpreadsheet($source, 'xlsx'))->first();

    expect($rows)->toHaveCount(1);
    expect($rows->first()->only(['device_name', 'device_ip'])->all())->toBe([
        'device_name' => 'core-sw-01',
        'device_ip' => '192.0.2.10',
    ]);
});
