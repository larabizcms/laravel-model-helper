<?php

namespace LarabizCMS\LaravelModelHelper\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * @property array $searchable
 * @method static static|Builder search(string $keyword)
 * @method static static|Builder additionSearch(string $keyword, bool $first)
 */
trait Searchable
{
    /**
     * Scope a query to search for a given keyword.
     *
     * The searchable fields are defined in the model's $searchable property.
     * Each field is an associative array with the following keys:
     * - 'column': the column name in the database
     * - 'operator': the operator to use for the filter
     * - 'table': the table name in the database
     * - 'type': the type of the filter (either 'table' or 'relation')
     * - 'field': the field name in the request
     *
     * @param  static|Builder  $query
     * @param  string  $keyword
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $keyword): Builder
    {
        $search = $this->parseSearchableFields();

        return $query->where(
            /**
             * @param  Builder|static  $query
             * @return Builder
             */
            function (Builder $query) use ($keyword, $search) {
                $first = true;

                foreach ($search as $column) {
                    // If the model has a scope{$column}Searchable method, call it
                    $col = Str::ucfirst(Str::camel($column['column']));
                    if (method_exists($this, "scope{$col}Searchable")) {
                        $query->{"scope{$col}Searchable"}($keyword, $first);
                        continue;
                    }

                    $operator = $column['operator'] ?? 'LIKE';
                    if ($operator !== 'FULLTEXT') {
                        $method = $first ? 'where' : 'orWhere';
                        // Use the LIKE operator by default
                        $query->{$method}($column['column'], $operator, "%{$keyword}%");
                    }

                    $first = false;
                }

                $fullTextColumns = collect($search)->filter(
                    fn($column) => $column['operator'] === 'FULLTEXT'
                )->pluck('column')->toArray();

                if ($fullTextColumns) {
                    $method = $first ? 'whereFullText' : 'orWhereFullText';
                    $query->{$method}($fullTextColumns, $keyword);
                }

                if (method_exists($this, 'scopeAdditionSearch')) {
                    // Call the additionSearch method if it exists
                    $query->additionSearch($keyword, $first);
                }

                return $query;
            }
        );
    }

    /**
     * Parse the searchable fields for the model.
     *
     * The searchable fields are defined in the model's $searchable property.
     * Each field is an associative array with the following keys:
     * - 'column': the column name in the database
     * - 'operator': the operator to use for the filter
     * - 'table': the table name in the database
     * - 'type': the type of the filter (either 'table' or 'relation')
     * - 'field': the field name in the request
     *
     * @return array
     */
    public function parseSearchableFields(): array
    {
        $tlb = $this->getTable();

        return array_values(
            array_map(
                /**
                 * Format the searchable fields for the model.
                 *
                 * @param mixed $value
                 * @param string $key
                 * @return array
                 */
                function ($value, $key) use ($tlb) {
                    if (is_int($key)) {
                        // If the key is an integer, then the value is the column name
                        return [
                            'column' => "{$tlb}.{$value}",
                            'operator' => 'LIKE',
                        ];
                    }

                    // If the value is an array, then it contains additional information
                    // about the filter
                    return [
                        'column' => "{$tlb}.{$key}",
                        'operator' => Str::upper($value),
                    ];
                },
                $this->getSearchable(),
                array_keys($this->getSearchable())
            )
        );
    }

    public function getSearchable(): array
    {
        return $this->searchable ?? [];
    }
}
