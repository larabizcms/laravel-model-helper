<?php

namespace LarabizCMS\LaravelModelHelper\Traits;

use App\Exports\ModelImport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use LarabizCMS\LaravelModelHelper\PendingModelImport;

/**
 * @mixin Model
 */
trait Importable
{
    /**
     * Imports data from a file into the specified model using the PendingModelImport class.
     *
     * @param string|UploadedFile $file The file to import data from.
     * @param string $version The version of the import. Defaults to 'default'.
     * @return PendingModelImport The PendingModelImport instance used for the import operation.
     */
    public static function import(string|UploadedFile $file, string $version = 'default'): PendingModelImport
    {
        /** @var Model $this */
        return new PendingModelImport(static::class, $file, $version);
    }

    /**
     * Retrieves the fields that can be imported from the model.
     *
     * This function checks if the model has a `getExportFields` method and if so, it returns the result of that method.
     * If not, it filters the fillable fields of the model to exclude the hidden fields and returns the filtered array.
     *
     * @return array The fields that can be imported from the model.
     */
    public function getImportFields(): array
    {
        if (method_exists($this, 'getExportFields')) {
            return $this->getExportFields();
        }

        return array_filter(
            $this->getFillable(),
            fn ($column) => !in_array($column, $this->getHidden())
        );
    }

    /**
     * Retrieves the versions of the import that can be used.
     *
     * This function returns an array of import versions, where the key is the version name
     * and the value is the class name of the import model. The default version is 'default'
     * and the corresponding class is ModelImport.
     *
     * @return array An array of import versions.
     */
    public function getImportVersions(): array
    {
        return ['default' => ModelImport::class];
    }
}
