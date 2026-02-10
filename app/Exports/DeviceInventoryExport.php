<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DeviceInventoryExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $headers;

    public function __construct(array $data)
    {
        $this->data = $data;
        $this->headers = $this->determineHeaders($data);
        $this->data = $this->normalizeData($data, $this->headers);
    }

    /**
     * Return a collection of data to be exported.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->data);
    }

    /**
     * Define the headings for the exported file.
     */
    public function headings(): array
    {
        return $this->headers;
    }

    /**
     * Determine the union of all keys across all rows to generate consistent headers.
     */
    private function determineHeaders(array $data): array
    {
        $headers = [];

        foreach ($data as $row) {
            $headers = array_unique(array_merge($headers, array_keys($row)));
        }

        return $headers;
    }

    /**
     * Normalize the data to ensure all rows have the same keys as the headers.
     */
    private function normalizeData(array $data, array $headers): array
    {
        return array_map(function ($row) use ($headers) {
            return array_merge(array_fill_keys($headers, null), $row);
        }, $data);
    }
}
