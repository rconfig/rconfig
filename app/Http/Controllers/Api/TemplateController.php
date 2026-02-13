<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTemplateRequest;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TemplateController extends ApiBaseController
{
    public function __construct(Template $model, $modelname = 'template')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {

        $searchCols = ['fileName', 'templateName'];
        $perPage = (int) $request->perPage ?: 10;

        $query = QueryBuilder::for(Template::class)
            ->allowedFilters([
                AllowedFilter::custom('q', new FilterMultipleFields, implode(',', $searchCols)),
            ])
            ->allowedSorts(['id', 'fileName', 'templateName', 'created_at'])
            ->with(['device:id,device_name']);

        $result = $query->paginate($perPage);

        $result->getCollection()->transform(function ($item) {
            $item->fileName = basename($item['fileName']);

            return $item;
        });

        return response()->json($result);
    }

    public function store(StoreTemplateRequest $request)
    {
        $fileName = $this->sanitizeFileName($request['templateName']);

        $storage_dir = storage_path() . '/app/rconfig/templates/';
        $filePath = $storage_dir . $fileName;

        if (File::exists($filePath)) {
            throw new \Exception('Could not create file or write to templates location: ' . $filePath . PHP_EOL);
        }

        $request['fileName'] = '/app/rconfig/templates/' . $this->sanitizeFileName($fileName);
        $request['templateName'] = $request['templateName'];
        $request['description'] = $request['description'];

        $filePath = storage_path() . $request['fileName'];

        File::put($filePath, $request->code);

        return parent::storeResource($request->toDTO()->toArray(), 0);
    }

    public function show($id, $relationship = null, $withCount = null)
    {
        $result = parent::show($id);
        $result['code'] = File::get(storage_path() . $result['fileName']);
        $result['fileName'] = basename($result['fileName']);

        return response()->json($result);
    }

    public function update($id, StoreTemplateRequest $request)
    {

        $oldFilename = $request->fileName;

        if (File::exists(storage_path() . '/app/rconfig/templates/' . $oldFilename)) {
            File::delete(storage_path() . '/app/rconfig/templates/' . $oldFilename);
        }

        $fileName = $this->sanitizeFileName($request['templateName']);

        $storage_dir = storage_path() . '/app/rconfig/templates/';
        $filePath = $storage_dir . $fileName;

        if (File::exists($filePath)) {
            File::delete($filePath);
        }

        $request['fileName'] = '/app/rconfig/templates/' . $fileName;
        $request['templateName'] = $request['templateName'];
        $request['description'] = $request['description'];

        File::put($filePath, $request->code);
        if (! File::exists($filePath)) {
            throw new \Exception('Could not create file or write to templates location: ' . $filePath . PHP_EOL);
        }

        return parent::updateResource($id, $request->toDTO()->toArray(), 0);
    }

    public function destroy($id, $return = 0)
    {

        try {
            $template = Template::findOrFail($id);
            $filePath = $template->fileName;

            $template->delete();

            if (File::exists(storage_path() . $filePath)) {
                File::delete(storage_path() . $filePath);
            }

            return response()->json(['message' => 'Template deleted successfully.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred.',
            ], 500); // For other exceptions, use 500
        }
    }

    public function getDefaultTemplate()
    {
        if (File::exists(storage_path() . '/app/rconfig/templates/default.yml')) {
            return File::get(storage_path() . '/app/rconfig/templates/default.yml');
        }

        return $this->failureResponse('Could not read default.yml file from the path: ' . storage_path() . '/app/rconfig/templates/default.yml');
    }

    public function sanitizeFileName($fileName)
    {
        $reformatter = new \App\Services\Templates\TemplateReformatter;
        $filename = $reformatter->sanitizeFileName($fileName);

        return $filename;
    }

    public function reformatTemplateFile(Request $request)
    {
        $fileName = $request['fileName'];
        $storage_dir = storage_path() . '/app/rconfig/templates/';
        $filePath = $storage_dir . $fileName;

        $reformatter = new \App\Services\Templates\TemplateReformatter;
        $reformattedContent = $reformatter->reformatTemplateFile($filePath);

        if ($reformattedContent === false) {
            return $this->failureResponse('Failed to reformat template file: ' . $fileName);
        }

        return $reformattedContent;
    }
}
