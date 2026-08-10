<?php

namespace App\Http\Controllers;

use App\Exports\DeviceImportTemplateExport;
use App\Services\Utilities\PathContainmentService;
use App\Traits\RespondsWithHttpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileDownloadController extends Controller
{
    use RespondsWithHttpStatus;

    public function download_device_import_template()
    {
        $logmsg = 'Device import template downloaded';
        activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'downloader');

        return Excel::download(new DeviceImportTemplateExport, 'device_import_template.xlsx');
    }

    /**
     * Stream a previously generated export file back to the user.
     *
     * The requested name is reduced to a bare basename and the resolved path is
     * checked to sit inside the export directory, so a caller cannot walk out of
     * it. Every legitimate caller already links to a basename, so this is not a
     * behaviour change for the UI.
     */
    public function download_export(Request $request): BinaryFileResponse|JsonResponse
    {
        $filename = basename((string) $request->query('filename', ''));
        $path = export_path() . $filename;

        if ($filename !== '' && $this->isContainedInExportPath($path)) {
            $logmsg = 'File download: ' . $filename . ' was downloaded';
            activityLogIt(__CLASS__, __FUNCTION__, 'info', $logmsg, 'downloader');

            return response()->download($path);
        } else {
            $logmsg = 'FILE DOWNLOAD: ' . $filename . ' could not be downloaded';
            activityLogIt(__CLASS__, __FUNCTION__, 'warn', $logmsg, 'downloader');

            $responseArray = ['error' => 404, 'message' => $logmsg];

            return \Response::json($responseArray);
        }
    }

    /**
     * Confirm the path resolves to a real file physically inside the export directory.
     *
     * @see PathContainmentService for the traversal and symlink handling
     */
    private function isContainedInExportPath(string $path): bool
    {
        return (new PathContainmentService)->resolveFileWithin(export_path(), $path) !== null;
    }
}
