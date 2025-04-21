<?php

namespace MariombnIo\IaQueryBuilder\Providers;

use Illuminate\Support\ServiceProvider;
use MariombnIo\IaQueryBuilder\Contracts\LlmProviderInterface;
use MariombnIo\IaQueryBuilder\Llm\OllamaProvider;

class IaQueryBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../Config/ai-query.php',
            'ai-query'
        );

        $this->app->bind(LlmProviderInterface::class, function () {
            $config = config('ai-query.llm');

            return match ($config['provider']) {
                'ollama' => new OllamaProvider(
                    $config['ollama']['model'],
                    $config['ollama']['base_url']
                ),
                default => throw new \InvalidArgumentException('Provider inválido: ' . $config['provider']),
            };
        });
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../Config/ai-query.php' => config_path('ai-query.php'),
        ], 'ia-query-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                \MariombnIo\IaQueryBuilder\Commands\GenerateCacheCommand::class,
            ]);
        }
    }
}
