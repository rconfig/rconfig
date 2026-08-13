<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTemplateRequest;
use App\Models\Template;
use App\Services\Templates\TemplateReformatter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
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
            ->allowedFilters(...[
                AllowedFilter::custom('q', new FilterMultipleFields, implode(',', $searchCols)),
            ])
            ->allowedSorts(...['id', 'fileName', 'templateName', 'created_at'])
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

    /**
     * Return a template record along with the contents of its yml file.
     *
     * A template row can outlive the file it points at, most often on an install
     * whose storage tree is incomplete, for example a restored backup or a
     * container started against an unseeded volume. That used to surface as a
     * 500 from an unguarded read. The record is now returned with empty code and
     * a fileMissing flag so the caller can say something useful. The absolute
     * path stays in the log rather than the response body.
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $result = parent::show($id);
        $templateFile = storage_path() . $result['fileName'];
        $result['fileMissing'] = ! File::exists($templateFile);

        if ($result['fileMissing']) {
            $result['code'] = '';
            activityLogIt(__CLASS__, __FUNCTION__, 'warn', 'Template file missing on disk: ' . $templateFile, 'templates');
        } else {
            $result['code'] = File::get($templateFile);
        }

        $result['fileName'] = basename($result['fileName']);

        return response()->json($result);
    }

    /**
     * Replace a template, removing the file the record currently points at.
     *
     * The name of the outgoing file is taken from the stored record rather than
     * from the request, so a caller cannot nominate a file outside the templates
     * directory for deletion. It is reduced to a basename as well, so a record
     * written by some other path cannot escape either.
     */
    public function update($id, StoreTemplateRequest $request)
    {
        $template = Template::findOrFail($id);

        $storage_dir = storage_path() . '/app/rconfig/templates/';

        $oldFilename = basename((string) $template->fileName);
        if ($oldFilename !== '' && File::exists($storage_dir . $oldFilename)) {
            File::delete($storage_dir . $oldFilename);
        }

        $fileName = $this->sanitizeFileName($request['templateName']);

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

            $template->delete();

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
        $reformatter = new TemplateReformatter;
        $filename = $reformatter->sanitizeFileName($fileName);

        return $filename;
    }

    public function reformatTemplateFile(Request $request)
    {
        $fileName = $request['fileName'];
        $storage_dir = storage_path() . '/app/rconfig/templates/';
        $filePath = $storage_dir . $fileName;

        $reformatter = new TemplateReformatter;
        $reformattedContent = $reformatter->reformatTemplateFile($filePath);

        if ($reformattedContent === false) {
            return $this->failureResponse('Failed to reformat template file: ' . $fileName);
        }

        return $reformattedContent;
    }
}
