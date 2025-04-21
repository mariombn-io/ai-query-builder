<?php

namespace MariombnIo\IaQueryBuilder;

use MariombnIo\IaQueryBuilder\Contracts\LlmProviderInterface;
use MariombnIo\IaQueryBuilder\Prompt\PromptTemplateBuilder;

class PromptProcessor
{
    protected LlmProviderInterface $provider;
    protected PromptTemplateBuilder $templateBuilder;

    public function __construct(LlmProviderInterface $provider)
    {
        $this->provider = $provider;
        $this->templateBuilder = new PromptTemplateBuilder();
    }

    public function generate(string $userPrompt): string
    {
        $fullPrompt = $this->templateBuilder->build($userPrompt);

        file_put_contents(storage_path('app/ai-query/last-prompt.txt'), $fullPrompt);

        return $this->provider->sendPrompt($fullPrompt);
    }
}
