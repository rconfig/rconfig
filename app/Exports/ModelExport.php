<?php

namespace App\Exports;

use App\Casts\EncryptStringCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ModelExport implements FromCollection, WithChunkReading, WithHeadings
{
    /** @var class-string<Model> */
    protected string $model;

    /**
     * @param  class-string<Model>  $modelName
     */
    public function __construct(string $modelName)
    {
        $this->model = $modelName;
    }

    public function collection(): Collection
    {
        if ($this->model == 'App\Models\Device') {
            return $this->set_device_data();
        }

        return $this->model::query()->get()->makeHidden($this->encryptedAttributes());
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

        return array_keys($this->model::first()?->makeHidden($this->encryptedAttributes())->toArray() ?? []);
    }

    /**
     * Attributes stored encrypted are decrypted by their cast on the way out, so they never belong in an export.
     *
     * @return list<string>
     */
    private function encryptedAttributes(): array
    {
        return array_keys(array_filter(
            (new $this->model)->getCasts(),
            fn (string $cast): bool => $cast === EncryptStringCast::class || str_starts_with($cast, 'encrypted')
        ));
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

        return collect($devices->setHidden(['view_url'])->makeHidden($this->encryptedAttributes()));
    }

    private function set_device_columns()
    {
        $columns = collect($this->model::first()?->makeHidden($this->encryptedAttributes()))->keys()->toArray();
        unset($columns[array_search('view_url', $columns)]);
        $columns[] = 'tags';

        return $columns;
    }
}
