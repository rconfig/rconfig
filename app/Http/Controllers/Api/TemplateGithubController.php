<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Utilities\PathContainmentService;
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

    /**
     * Directory under templates_path() that the templates repository is cloned into.
     * Every path a caller sends back to this controller must resolve inside it.
     */
    private const REPO_DIRECTORY = 'rConfig-templates';

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
            $result = 'Successfully downloaded ' . count(File::allFiles(templates_path() . self::REPO_DIRECTORY)) . ' templates from \'github.com/rconfig/rconfig-templates\' Github repo';

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
            $result = $response->json();
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
        $dstDir = templates_path() . self::REPO_DIRECTORY;

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

    /**
     * List the yml templates in one folder of the cloned repository.
     *
     * The caller passes back an absolute path taken from list_template_repo_folders(), so the
     * value is checked to sit inside the cloned repository before it is globbed. Without that
     * the endpoint enumerates yml files in any directory on the host.
     */
    public function list_repo_folders_contents(Request $request)
    {
        $directory = (new PathContainmentService)->resolveDirectoryWithin(
            templates_path() . self::REPO_DIRECTORY,
            (string) $request->input('directory', '')
        );

        if ($directory === null) {
            $result['msg'] = 'Unable to return list of yml templates! Check the application logs!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->failureResponse($result);
        }

        $origListofTemplateFiles = File::glob($directory . '/*.yml');

        // get README.md file
        File::exists($directory . '/README.md') ? $readmeFile = File::glob($directory . '/README.md') : $readmeFile = [];

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

    /**
     * Return the contents of one template file from the cloned repository.
     *
     * As with list_repo_folders_contents(), the caller passes back an absolute path that came
     * from this controller, so it is checked for containment and for a yml extension before
     * being read. The parse is wrapped because a malformed file previously produced a 500,
     * and the exception message embeds a snippet of the file it failed on.
     */
    public function get_template_file_contents(Request $request)
    {
        $templateFile = (new PathContainmentService)->resolveFileWithin(
            templates_path() . self::REPO_DIRECTORY,
            (string) $request->input('filepath', '')
        );

        if ($templateFile === null || ! in_array(strtolower(pathinfo($templateFile, PATHINFO_EXTENSION)), ['yml', 'yaml'], true)) {
            $result['msg'] = 'Unable to return template content! Check the application logs!';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $result['msg'], 'templates');

            return $this->failureResponse($result);
        }

        try {
            $template['code'] = File::get($templateFile);
            $yamlContents = Yaml::parse($template['code']);
            $template['templateName'] = $yamlContents['main']['name'];
            $template['description'] = $yamlContents['main']['desc'];
        } catch (\Throwable $e) {
            $result['msg'] = 'Unable to return template content! Check the application logs!';
            activityLogIt(__CLASS__, __FUNCTION__, 'warn', $result['msg'] . ' ' . $e->getMessage(), 'templates');

            return $this->failureResponse($result);
        }

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
