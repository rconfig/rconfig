<?php

namespace App\Http\Controllers\Connections\Params;

use Symfony\Component\Yaml\Yaml;

/**
 * Loads & Stores template, per devicesParams object in an object, configuration parameters as passed form rConfig Templates file loaded for this specific devices templateId
 * Test Covered in BasicCommandsTest
 *
 * @author Stephen_Stack
 */
class ConnectionParams
{
    private $templateId;

    public function __construct(int $templateId)
    {
        $this->templateId = $templateId;
    }

    public function getTemplateParams()
    {
        $templateArray = $this->getTemplateArray();

        return $this->templateToArray($templateArray['code']);
    }

    private function getTemplateArray()
    {
        $loadtemplate = new LoadTemplate($this->templateId);

        return json_decode($loadtemplate->load(), true);
    }

    private function templateToArray($code)
    {
        $yamlContents = Yaml::parse($code);

        return $yamlContents;
    }
}
