<?php

namespace LarabizCMS\LaravelModelHelper;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PendingModelExport implements Responsable
{
    protected bool $export = false;

    protected string $format = \Maatwebsite\Excel\Excel::XLSX;

    /**
     * @param  Model  $model
     * @param  string  $fileName
     * @param  array  $params
     * @param  string  $version
     */
    public function __construct(
        protected string $model,
        protected string $fileName,
        protected array $params = [],
        protected string $version = 'default'
    ) {
    }

    /**
     * Sets the file name for the pending model export.
     *
     * @param  string  $fileName  The name of the file.
     * @return static Returns the current instance of the class.
     */
    public function fileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Sets the format for the pending model export.
     *
     * @param  string  $format  The format to be set.
     * @return static Returns the current instance of the class.
     */
    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Export the model data to a file and return the response.
     *
     * @return Response|BinaryFileResponse The response containing the exported file.
     */
    public function export(): Response|BinaryFileResponse
    {
        $this->export = true;

        return Excel::download(
            new ($this->getExportVersion()['class'])($this->model, $this->params),
            $this->fileName,
            $this->format
        );
    }

    public function store(): bool
    {
        $this->export = true;

        return Excel::store(
            new ($this->getExportVersion()['class'])($this->model, $this->params),
            $this->fileName,
            $this->format
        );
    }

    public function queue(): PendingDispatch
    {
        $this->export = true;

        return Excel::queue(
            new ($this->getExportVersion()['class'])($this->model, $this->params),
            $this->fileName,
            $this->format
        );
    }

    public function toResponse($request): Response|BinaryFileResponse
    {
        return $this->export();
    }

    public function __destruct()
    {
        if (!$this->export) {
            $this->export();
        }
    }

    protected function getExportVersion(): array
    {
        $version = app($this->model)->getExportVersions()[$this->version];
        if (is_string($version)) {
            return [
                'label' => Str::title(Str::replace(['_','-'], ' ', $this->version)),
                'class' => $version,
            ];
        }

        return $version;
    }
}
