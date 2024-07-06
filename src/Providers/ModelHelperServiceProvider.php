<?php

namespace LarabizCMS\LaravelModelHelper\Providers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Stringable;
use Maatwebsite\Excel\Concerns\WithCustomQuerySize;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Events\BeforeWriting;
use Maatwebsite\Excel\Writer;
use LarabizCMS\LaravelModelHelper\CacheGroup;
use LarabizCMS\LaravelModelHelper\Contracts\CacheGroup as QueriesCacheGroup;

class ModelHelperServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(QueriesCacheGroup::class, fn ($app) => new CacheGroup($app['cache']));

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'oc_model_helper');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Writer::listen(
            BeforeExport::class,
            function ($event) {
                if (method_exists($event->getConcernable(), 'queueWithProgress')) {
                    if ($event->getConcernable() instanceof WithCustomQuerySize) {
                        $total = $event->getConcernable()->querySize();
                    } else {
                        $total = $event->getConcernable()->query()->count();
                    }

                    $event->getConcernable()->progress->listenBeforeExport($total);
                }
            }
        );

        Writer::listen(
            BeforeWriting::class,
            function ($event) {
                if (method_exists($event->getConcernable(), 'queueWithProgress')) {
                    $chunkSize = config('excel.exports.chunk_size', 1000);

                    if (method_exists($event->getConcernable(), 'chunkSize')) {
                        $chunkSize = $event->getConcernable()->chunkSize();
                    }

                    $event->getConcernable()->progress->listenBeforeWriting($chunkSize);
                }
            }
        );

        Storage::disk('local')->buildTemporaryUrlsUsing(
            function ($path, $expiration, $options) {
                return URL::temporarySignedRoute(
                    'admin.files.download',
                    $expiration,
                    array_merge($options, ['path' => $path])
                );
            }
        );

        $this->macroForDev();
    }

    private function macroForDev(): void
    {
        $rawBuilderCallback = function ($sql, $binding) {
            switch (1) {
                case is_int($binding):
                case is_bool($binding):
                    $binding = (int) $binding;
                    break;
                case is_string($binding):
                    $binding = "'" . $binding . "'";
                    break;
                case $binding instanceof Stringable:
                    $binding = "'" . $binding->toString() . "'";
                    break;
                case $binding instanceof \Stringable:
                    $binding = "'" . $binding->__toString() . "'";
                    break;
                case $binding instanceof Carbon:
                    $binding = "'". $binding->format('Y-m-d H:i:s') ."'";
                    break;
            }

            return preg_replace(
                '/\?/',
                $binding,
                $sql,
                1
            );
        };

        Builder::macro(
            'toRawSql',
            function () use ($rawBuilderCallback) {
                /** @var Builder $this */
                return array_reduce(
                    $this->getBindings(),
                    $rawBuilderCallback,
                    $this->toSql()
                );
            }
        );

        EloquentBuilder::macro(
            'toRawSql',
            function () use ($rawBuilderCallback) {
                /** @var EloquentBuilder $this */
                return array_reduce(
                    $this->getBindings(),
                    $rawBuilderCallback,
                    $this->toSql()
                );
            }
        );

        Builder::macro(
            'ddRawSql',
            function () {
                /** @var Builder $this */
                dd($this->toRawSql());
            }
        );

        EloquentBuilder::macro(
            'ddRawSql',
            function () {
                /** @var EloquentBuilder $this */
                dd($this->toRawSql());
            }
        );
    }
}
