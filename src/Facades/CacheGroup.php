<?php
/**
 * LARABIZ CMS - Full SPA Laravel CMS
 *
 * @package    larabizcms/larabiz
 * @author     The Anh Dang
 * @link       https://larabiz.com
 */

namespace LarabizCMS\LaravelModelHelper\Facades;

use Illuminate\Support\Facades\Facade;
use LarabizCMS\LaravelModelHelper\Contracts\CacheGroup as CacheGroupContract;

class CacheGroup extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return CacheGroupContract::class;
    }
}
