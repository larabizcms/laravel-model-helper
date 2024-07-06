<?php

namespace LarabizCMS\LaravelModelHelper\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use OneContent\LaravelPlus\Resources\CollectionResource;
use OneContent\LaravelPlus\Resources\ModelResource;

trait HasResource
{
    /**
     * Get the resource class for the model.
     *
     * @return class-string<JsonResource>
     */
    public static function resource(): string
    {
        return ModelResource::class;
    }

    /**
     * Get the collection resource class for the model.
     *
     * @return class-string<ResourceCollection>
     */
    public static function collectionResource(): string
    {
        return CollectionResource::class;
    }

    /**
     * Make a new resource collection.
     *
     * @param mixed $resource
     * @return JsonResource
     */
    public static function makeResource($resource): JsonResource
    {
        return static::resource()::make($resource);
    }

    /**
     * Make a new resource collection.
     *
     * If the resource is not a CollectionResource and the resource class
     * is not a ModelResource, then return a new CollectionResource with
     * the given resource.
     *
     * If the resource is a CollectionResource or the resource class is a
     * ModelResource, then return a new resource collection using the
     * resource class.
     *
     * @param  mixed  $resource
     * @return ResourceCollection
     */
    public static function makeCollectionResource($resource): ResourceCollection
    {
        // If the resource is not a CollectionResource and the resource class
        // is not a ModelResource, then return a new CollectionResource with
        // the given resource.
        if (static::collectionResource() === CollectionResource::class
            && static::resource() !== ModelResource::class
        ) {
            return static::resource()::collection($resource);
        }

        // If the resource is a CollectionResource or the resource class is a
        // ModelResource, then return a new resource collection using the
        // resource class.
        return static::collectionResource()::make($resource);
    }
}
