<?php

namespace LarabizCMS\LaravelModelHelper\Traits\Exports;

use Illuminate\Foundation\Bus\PendingDispatch;
use LarabizCMS\LaravelModelHelper\ExcelProgress;
use Throwable;

trait WithQueueExportProgress
{
    public ExcelProgress $progress;

    public function failed(Throwable $exception): void
    {
        $this->progress->failed($exception);
    }

    public function queueWithProgress(
        ExcelProgress $progress,
        string $filePath = null,
        string $disk = null,
        string $writerType = null,
        $diskOptions = []
    ): PendingDispatch {
        $this->progress = $progress;

        $this->progress->setOption('filePath', $filePath);
        $this->progress->setOption('fileDisk', $disk);

        return $this->queue($filePath, $disk, $writerType, $diskOptions);
    }
}
