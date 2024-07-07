<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcom/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace LarabizCMS\LaravelModelHelper\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModelCollectionResource extends ResourceCollection
{
    public $with = ['success' => true];

    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  Request  $request
     * @return array
     */
    // public function paginationInformation($request, $paginated, $default): array
    // {
    //     return [
    //         'pagination' => [
    //             'current_page' => $paginated['current_page'],
    //             'from' => $paginated['from'],
    //             'last_page' => $paginated['last_page'],
    //             'per_page' => $paginated['per_page'],
    //             'to' => $paginated['to'],
    //             'total' => $paginated['total'],
    //         ]
    //     ];
    // }
}
