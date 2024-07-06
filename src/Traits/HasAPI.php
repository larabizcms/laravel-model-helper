<?php

namespace LarabizCMS\LaravelModelHelper\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @method static Builder|static api(array $params)
 * @method static Builder|static inApi(array $params) // TODO: Add your custom scopes here
 */
trait HasAPI
{
    use Sortable, Searchable, Filterable, HasResource;

    /**
     * TODO: Add your custom scopes here
     *
     * @param  Builder  $builder
     * @param  array  $params
     * @return Builder
     */
    // public function scopeInApi(Builder $builder, array $params): Builder
    // {
    // }

    public function apiWithDefaults(): array
    {
        return [];
    }

    /**
     * API scope, call this scope for API query
     *
     * @param  Builder|static  $builder
     * @param  array  $params
     * @return Builder
     */
    public function scopeApi(Builder $builder, array $params): Builder
    {
        return $builder->with($this->apiWithDefaults())
            ->when(
                method_exists($this, 'scopeInApi'),
                fn (Builder $query) => $query->inApi($params)
            )
            ->when(
                method_exists($this, 'scopeInApiGuest') && auth()->guest(),
                fn (Builder $query) => $query->inApiGuest($params)
            )
            ->when(
                $keyword = Arr::get($params, 'q'),
                fn (Builder $query) => $query->search($keyword)
            )
            ->filter($params)
            ->sort($params);
    }
}
