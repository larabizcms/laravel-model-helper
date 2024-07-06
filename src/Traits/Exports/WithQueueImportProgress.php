<?php

namespace LarabizCMS\LaravelModelHelper\Traits\Exports;

use LarabizCMS\LaravelModelHelper\ExcelProgress;
use Throwable;

trait WithQueueImportProgress
{
    public ExcelProgress $progress;

    public function failed(Throwable $exception): void
    {
        $this->progress->failed($exception);
    }
}
