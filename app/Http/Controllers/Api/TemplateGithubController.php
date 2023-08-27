<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Yaml\Yaml;

class TemplateGithubController extends Controller
{
    use RespondsWithHttpStatus;

    private $username;

    private $repo;

    public function __construct()
    {
        $this->username = Config::get('github.git.rconfig-username');
        $this->repo = Config::get('github.git.rconfig-template-repo');
    }

    public function import_github_templates()
    {
        Artisan::call('rconfig:clone-templates');
        $result = Artisan::output();
        $arr = explode("\n", $result);

        if (count($arr) > 0) {
            $result = 'Successfully downloaded ' . count(File::allFiles(templates_path() . 'rConfig-templates')) . ' templates from \'github.com/rconfig/rconfig-templates\' Github repo';

            return $this->successResponse('Success', $result);
        } else {
            $result = 'Exception thrown: Could not connect to repo ';

            return $this->failureResponse($result);
        }
    }

    public function test_github_repo_connection()
    {
        try {
            //  https://api.github.com/repos/OWNER/REPO/contents/PATH
            $response = Http::get('https://api.github.com/repos/' . $this->username . '/' . $this->repo . '/contents/')->throw();
            // dd($response->json());
            $result['data'] = $response->json();
            $result['msg'] = 'Successfully connected to rConfig Templates Github repo';

            return $this->successResponse('Success', $result);
        } catch (\Exception $e) {
            $result['data'] = '';
            $result['msg'] = 'Exception thrown: Could not connect to repo - ' . $e->getMessage();

            return $this->failureResponse($result);
        }
    }

    public function list_template_repo_folders()
    {
        $dirsArray = [];
        $dstDir = templates_path() . 'rConfig-templates';

        if (is_dir($dstDir)) {
            $origdirsArray = File::directories($dstDir);
            foreach ($origdirsArray as $key => $file) {
                $dirsArray[$key]['path'] = $file;
                $dirsArray[$key]['name'] = basename($file);
            }

            $result['data'] = $dirsArray;
            $result['msg'] = 'Found cloned templates repo folder!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->successResponse('Success', $result);
        } else {
            $result['msg'] = 'rConfig-templates is empty, or does not exist. Clone from "https://github.com/rconfig/rconfig-templates" may have failed! Try importing the templates again.!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->failureResponse($result);
        }
    }

    public function list_repo_folders_contents(Request $request)
    {
        $origListofTemplateFiles = File::glob("{$request->directory}/*.yml");

        // get README.md file
        File::exists("{$request->directory}/README.md") ? $readmeFile = File::glob("{$request->directory}/README.md") : $readmeFile = [];

        if (count($origListofTemplateFiles) > 0) {
            foreach ($origListofTemplateFiles as $key => $file) {
                $listofFiles[$key]['path'] = $file;
                $listofFiles[$key]['name'] = basename($file);
            }
            $result['data'] = $listofFiles;
            $result['msg'] = 'List of yml templates returned!';

            if (count($readmeFile) > 0) {
                $result['readme']['path'] = $readmeFile[0];
                $result['readme']['name'] = basename($readmeFile[0]);
            }

            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->successResponse('Success', $result);
        } else {
            $result['msg'] = 'Unable to return list of yml templates! Check the application logs!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->failureResponse($result);
        }
    }

    public function get_template_file_contents(Request $request)
    {
        $template['code'] = File::get($request->filepath);
        $yamlContents = Yaml::parse($template['code']);
        $template['templateName'] = $yamlContents['main']['name'];
        $template['description'] = $yamlContents['main']['desc'];
        if (count($template) > 0) {
            $result['data'] = $template;
            $result['msg'] = 'Template content returned!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->successResponse('Success', $result);
        } else {
            $result['msg'] = 'Unable to return template content! Check the application logs!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->failureResponse($result);
        }
    }
}
