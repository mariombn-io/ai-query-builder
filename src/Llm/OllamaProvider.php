<?php

namespace MariombnIo\IaQueryBuilder\Llm;


use Illuminate\Support\Facades\Http;
use MariombnIo\IaQueryBuilder\Contracts\LlmProviderInterface;

class OllamaProvider implements LlmProviderInterface
{
    protected string $model;
    protected string $baseUrl;

    public function __construct(string $model = 'llama3', string $baseUrl = 'http://localhost:11434')
    {
        $this->model = $model;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function sendPrompt(string $prompt, array $context = []): string
    {
        $response = Http::timeout(120)->post("{$this->baseUrl}/api/generate", [
            'model' => $this->model,
            'prompt' => $prompt,
            'stream' => false,
        ]);

        return $response->json('response') ?? '';
    }
}
