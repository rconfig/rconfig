<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreTemplateRequest;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

class TemplateController extends ApiBaseController
{
    public function __construct(Template $model, $modelname = 'template')
    {
        $this->model = $model;
        $this->modelname = $modelname;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $searchCols = null, $relationship = null, $withCount = null)
    {
        $searchCols = ['fileName', 'templateName'];
        $result = parent::index($request, $searchCols);

        $result->getCollection()->transform(function ($item) {
            $item->fileName = basename($item['fileName']);

            return $item;
        });

        return response()->json($result);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTemplateRequest $request)
    {
        $filedata = $this->prepareYamlFile($request);

        $request['fileName'] = '/app/rconfig/templates/'.$filedata['fileName'];
        $request['templateName'] = $filedata['templateName'];
        $request['description'] = $filedata['description'];

        File::put($filedata['filePath'], $request->code);
        if (! File::exists($filedata['filePath'])) {
            throw new \Exception('Could create file or write to templates location: '.$filedata['filePath'].PHP_EOL);
        }

        return parent::storeResource($request->toDTO()->toArray(), 0);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function show($id, $relationship = null, $withCount = null)
    {
        $result = parent::show($id);
        $result['code'] = File::get(storage_path().$result['fileName']);
        $result['fileName'] = basename($result['fileName']);

        return response()->json($result);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Template  $template
     * @return \Illuminate\Http\Response
     */
    public function update($id, StoreTemplateRequest $request)
    {
        $filedata = $this->prepareYamlFile($request);

        if (File::exists($filedata['filePath'])) {
            File::delete($filedata['filePath']);
        }

        File::put($filedata['filePath'], $request->code);
        if (! File::exists($filedata['filePath'])) {
            throw new \Exception('Could create file or write to templates location: '.$filedata['filePath'].PHP_EOL);
        }

        $request['fileName'] = '/app/rconfig/templates/'.$filedata['fileName'];

        $request['templateName'] = $filedata['templateName'];
        $request['description'] = $filedata['description'];

        return parent::updateResource($id, $request->toDTO()->toArray(), 0);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $return = 0)
    {
        $model = parent::destroy($id, 1);

        $filePath = $model->fileName;

        $model->delete();

        if (File::exists(storage_path().$filePath)) {
            File::delete(storage_path().$filePath);
        }

        return $this->successResponse(Str::ucfirst($this->modelname).' deleted successfully!');
    }

    public function getDefaultTemplate()
    {
        if (File::exists(storage_path().'/app/rconfig/templates/default.yml')) {
            return File::get(storage_path().'/app/rconfig/templates/default.yml');
        }

        return $this->failureResponse('Could not read default.yml file from the path: '.storage_path().'/app/rconfig/templates/default.yml');
    }

    private function prepareYamlFile($request)
    {
        $yamlContents = Yaml::parse($request->code);
        $fileName = str_replace(' ', '_', $request->fileName);
        $fileNameArr = pathinfo($fileName);
        if (! isset($fileNameArr['extension']) || $fileNameArr['extension'] != 'yml') {
            $fileName = $fileName.'.yml';
        }
        $storage_dir = storage_path().'/app/rconfig/templates/';
        $filePath = $storage_dir.$fileName;

        return $filedata = [
            'filePath' => $filePath,
            'fileName' => $fileName,
            'templateName' => $yamlContents['main']['name'],
            'description' => $yamlContents['main']['desc'],
        ];
    }
}
