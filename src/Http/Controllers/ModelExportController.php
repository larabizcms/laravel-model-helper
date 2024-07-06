<?php

namespace LarabizCMS\LaravelModelHelper\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use LarabizCMS\LaravelModelHelper\ExcelProgress;
use OneContent\Project\Exports\ExportCheckin;
use OneContent\Project\Exports\ExportPromotion;
use OneContent\Project\Models\Project;

class ModelExportController extends Controller
{
    public function export()
    {
        $project = Project::find(1);
        $user = auth()->user();
        $progress = new ExcelProgress();
        // Dispatch the job to write to file
        (new ExportCheckin($project, $user, []))
            ->queueWithProgress($progress, 'promotions-'. date('Y-m-d-H-i-s') .'.xlsx')
            ->allOnQueue('export');

        return view('oc_model_helper::stream-response', compact('progress'));
    }

    public function import()
    {
        //
    }

    public function processed(string $key)
    {
        $progress = new ExcelProgress($key);

        if (!file_exists($progress->tmpFile())) {
            abort(404);
        }

        if (ob_get_level() == 0) {
            ob_start();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');

        while (true) {
            $contents = file_get_contents($progress->tmpFile());

            echo $contents;

            if (($json = json_decode($contents))) {
                if (isset($json->error)) {
                    File::delete($progress->tmpFile());
                    ob_flush();
                    flush();
                    sleep(1);
                    exit();
                }

                if ($json->processed >= 100) {
                    ob_flush();
                    flush();
                    sleep(1);
                    break;
                }
            }

            ob_flush();
            flush();
            sleep(1);
        }

        $options = $progress->getOptions();
        $filePath = $options['filePath'];
        $fileDisk = $options['fileDisk'];

        $options['message'] = 'File is being created...';
        $options['progressing'] = $options['total'];
        $options['processed'] = 100;

        while (! Storage::disk($fileDisk)->exists($filePath)) {
            echo json_encode($options);
            ob_flush();
            flush();
            sleep(1);
        }

        unset($options['message']);
        $options['done'] = true;
        $options['fileUrl'] = Storage::disk($fileDisk)->temporaryUrl($filePath, now()->addHour());
        echo json_encode($options);
        File::delete($progress->tmpFile());
        ob_flush();
        flush();
    }
}
