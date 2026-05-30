<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModelExport implements FromCollection, WithChunkReading, WithHeadings
{
    protected $model;

    public function __construct($ModelName)
    {
        $this->model = $ModelName;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        if ($this->model == 'App\Models\Device') {
            return $this->set_device_data();
        }

        return $this->model::all();
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function headings(): array
    {
        if ($this->model == 'App\Models\Device') {
            return $this->set_device_columns();
        }

        return collect($this->model::first())->keys()->toArray();
    }

    private function set_device_data()
    {
        // if table is device, then $this->model is App\Models\Device and we want to return the query with tags
        $devices = $this->model::with('tag')->get();
        $devices->each(function ($device) {
            $device->tags = implode(', ', $device->tag->pluck('tagname')->toArray());
            unset($device->view_url);
            unset($device->tag);
        });

        return collect($devices->setHidden(['view_url']));
    }

    private function set_device_columns()
    {
        $columns = collect($this->model::first())->keys()->toArray();
        unset($columns[array_search('view_url', $columns)]);
        $columns[] = 'tags';

        return $columns;
    }
}
