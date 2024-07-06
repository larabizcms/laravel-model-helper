<?php

namespace LarabizCMS\LaravelModelHelper\Traits;

use App\Exports\ModelExport;
use LarabizCMS\LaravelModelHelper\PendingModelExport;

trait Exportable
{
    /**
     * Export the model to an Excel/CSV file.
     *
     * @param  string|null  $fileName  The file name to export to.
     * @param  array  $params  The parameters to pass to the export.
     * @param  string  $version
     * @return PendingModelExport
     */
    public static function export(
        ?string $fileName = null,
        array $params = [],
        string $version = 'default'
    ): PendingModelExport {
        // If the file name is not provided, use the table name as the default file name.
        $fileName = $fileName ?? (new static())->getTable() . '.xlsx';

        // Create a new instance of the PendingModelExport class with the
        // current model instance, the file name and the parameters.
        return new PendingModelExport(static::class, $fileName, $params, $version);
    }

    /**
     * Retrieves the export fields for the current model instance.
     *
     * This function filters the fillable columns of the model instance to exclude
     * any columns that are hidden. It returns an array of the remaining columns.
     *
     * @return array The array of export fields.
     */
    public function getExportFields(): array
    {
        return array_filter(
            $this->getFillable(),
            fn ($column) => !in_array($column, $this->getHidden())
        );
    }

    public function getExportVersions(): array
    {
        return ['default' => ModelExport::class];
    }
}
