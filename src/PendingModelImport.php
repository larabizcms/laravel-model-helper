<?php

namespace LarabizCMS\LaravelModelHelper;

use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Excel as MaatwebsiteExcel;
use Maatwebsite\Excel\Facades\Excel;

class PendingModelImport
{
    protected bool $imported = false;

    protected ?string $disk = null;

    protected ?string $readerType = null;

    /**
     * Constructs a new instance of the class.
     *
     * @param string $model The model to be used.
     * @param string|UploadedFile $file The file to be used.
     * @param string $version The version to be used. Defaults to 'default'.
     */
    public function __construct(
        protected string $model,
        protected string|UploadedFile $file,
        protected string $version = 'default'
    ) {
    }

    /**
     * Sets the disk to be used for the import operation.
     *
     * @param string $disk The name of the disk.
     * @return static Returns the current instance of the class.
     */
    public function onDisk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * Sets the reader type for the object.
     *
     * @param string $readerType The type of reader to set.
     * @return static Returns the current instance of the object.
     */
    public function readerType(string $readerType): static
    {
        $this->readerType = $readerType;

        return $this;
    }

    /**
     * Imports data from an Excel file into the specified model using the Maatwebsite Excel library.
     *
     * @return MaatwebsiteExcel The Maatwebsite Excel instance used for the import operation.
     */
    public function import(): MaatwebsiteExcel
    {
        $this->imported = true;

        return Excel::import(
            new ($this->getImportVersion()['class'])($this->model),
            $this->file,
            $this->disk,
            $this->readerType
        );
    }

    /**
     * Queues the import of data from an Excel file into the specified model using the Maatwebsite Excel library.
     *
     * @return PendingDispatch The pending dispatch instance for the import operation.
     */
    public function queue(): PendingDispatch
    {
        $this->imported = true;

        return Excel::queueImport(
            new ($this->getImportVersion()['class'])($this->model),
            $this->file,
            $this->disk,
            $this->readerType
        );
    }

    public function __destruct()
    {
        if (! $this->imported) {
            $this->import();
        }
    }

    /**
     * Gets the version of the import to be used.
     *
     * @return array The version of the import to be used.
     */
    protected function getImportVersion(): array
    {
        $version = app($this->model)->getImportVersions()[$this->version];
        if (is_string($version)) {
            return [
                'label' => Str::title(Str::slug($this->version, ' ')),
                'class' => $version,
            ];
        }

        return $version;
    }
}
