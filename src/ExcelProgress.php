<?php

namespace LarabizCMS\LaravelModelHelper;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

class ExcelProgress implements Arrayable
{
    protected string $key;

    protected array $options = [];

    public function __construct(?string $key = null)
    {
        $this->key = $key ?? $this->generateKey();
    }

    public function listenBeforeExport(int $total): void
    {
        $json = $this->getOptions();
        $json['total'] = $total;
        $json['progressing'] = 0;
        $json['processed'] = 0;
        $this->setOptions($json);
    }

    public function listenBeforeWriting(int $chunkSize): void
    {
        $json = $this->getOptions();
        $json['progressing'] = $json['progressing'] + $chunkSize;

        $currentProgress = $json['progressing'] - $chunkSize;
        if ($currentProgress < 0) {
            $currentProgress = 0;
        }

        $json['processed'] = $json['total'] > 0 ? round($currentProgress / $json['total'] * 100) : 100;
        $this->setOptions($json);
    }

    public function tmpFile(?string $key = null): string
    {
        $key ??= $this->key;

        return storage_path("app/tmps/export-{$key}.json");
    }

    public function failed(Throwable $exception): void
    {
        $json = $this->getOptions();
        $json['error'] = $exception->getMessage();
        $this->setOptions($json);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function setOption(string $key, $value): static
    {
        $options = $this->getOptions();

        $options[$key] = $value;

        $this->setOptions($options);

        return $this;
    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        File::put($this->tmpFile(), json_encode($options));

        return $this;
    }

    public function getOptions(): array
    {
        $this->options = json_decode(File::get($this->tmpFile()), true);

        return $this->options;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'options' => $this->getOptions(),
        ];
    }

    protected function generateKey(): string
    {
        $key = Str::random(32);

        while (file_exists($this->tmpFile($key))) {
            $key = Str::random(32);
        }

        $this->options = ['processed' => 0, 'progressing' => 0];
        File::put($this->tmpFile($key), json_encode($this->options));

        return $key;
    }
}
