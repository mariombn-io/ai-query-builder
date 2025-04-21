<?php

namespace MariombnIo\IaQueryBuilder\Contracts;

interface LlmProviderInterface
{
    /**
     * Send prompt to the LLM provider and get the response.
     * @param string $prompt
     * @param array $context
     * @return string
     */
    public function sendPrompt(string $prompt, array $context = []): string;
}