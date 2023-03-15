<?php

namespace App\Http\Controllers\Connections\Params;

use App\Models\Template;
use File;

/**
 * Retrives template file from fielsystem, and loads to object
 * Test Covered in BasicCommandsTest
 *
 * @author Stephen_Stack
 */
class LoadTemplate
{
    private $templateId;

    public function __construct(int $templateId)
    {
        $this->templateId = $templateId;
    }

    public function load()
    {
        $path = Template::select('fileName')->where('id', $this->templateId)->get();
        // dd($path);
        if (count($path) === 0) {
            throw new \Exception('Template not found');
        }
        $fullpath = storage_path() . $path[0]->fileName;
        $templateArr['fileName'] = $this->getFileBasename($fullpath);
        $templateArr['code'] = $this->getTemplateCode($fullpath);

        return json_encode($templateArr);
    }

    private function getFileBasename($filePath)
    {
        return basename($filePath);
    }

    private function getTemplateCode($filePath)
    {
        return File::get($filePath);
    }
}
