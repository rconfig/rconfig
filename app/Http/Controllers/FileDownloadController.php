<?php

namespace App\Http\Controllers;

use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileDownloadController extends Controller
{
    use RespondsWithHttpStatus;

    public function download_export()
    {
        $path = export_path() . $_GET['filename'];
        if (file_exists($path)) {
            $logmsg = 'File download: ' . basename($path) . ' was downloaded';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'downloader');

            return response()->download($path);
        } else {
            $logmsg = 'FILE DOWNLOAD: ' . basename($path) . ' could not be downloaded';
            activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'downloader');

            $responseArray = ['error' => 404, 'message' => $logmsg];

            return \Response::json($responseArray);
        }
    }
}
