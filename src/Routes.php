<?php

namespace LarabizCMS\LaravelModelHelper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use LarabizCMS\LaravelModelHelper\Http\Controllers\ModelExportController;

class Routes
{
    public static function register(): void
    {
        Route::get('export', [ModelExportController::class, 'export']);
        Route::get('export/processed/{key}', [ModelExportController::class, 'processed'])->name('export.processed');
        Route::get(
            'local/temp/{path}',
            function (string $path) {
                return Storage::disk('local')->download($path);
            }
        )->name('files.download');
    }
}
