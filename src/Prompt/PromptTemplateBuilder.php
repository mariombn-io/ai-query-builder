<?php

namespace MariombnIo\IaQueryBuilder\Prompt;

use Illuminate\Support\Facades\File;

class PromptTemplateBuilder
{
    protected array $modelsData = [];

    public function __construct()
    {
        $this->loadModelsCache();
    }

    protected function loadModelsCache(): void
    {
        $cachePath = storage_path('app/ai-query/models-cache.json');

        if (!File::exists($cachePath)) {
            throw new \RuntimeException('Model cache not found. Please run: php artisan ia-query:generate-cache');
        }

        $this->modelsData = json_decode(File::get($cachePath), true);
    }

    public function build(string $userPrompt): string
    {
        $instructions = $this->instructions();

        $modelsList = '';
        foreach ($this->modelsData as $model => $info) {
            $columns = implode(', ', $info['columns']);
            $modelsList .= "- {$model} ({$columns})" . PHP_EOL;
        }

        $relationsList = '';
        foreach ($this->modelsData as $model => $info) {
            foreach ($info['relations'] as $relationName => $relationType) {
                $relationsList .= "- {$model} has {$relationType} relation via method '{$relationName}'" . PHP_EOL;
            }
        }

        return $instructions . PHP_EOL . PHP_EOL .
            'Context:' . PHP_EOL .
            'Models:' . PHP_EOL .
            $modelsList . PHP_EOL .
            'Relationships:' . PHP_EOL .
            ($relationsList ?: '(none)') . PHP_EOL . PHP_EOL .
            'Request:' . PHP_EOL .
            $userPrompt;
    }

    protected function instructions(): string
    {
        return implode(PHP_EOL, [
            'You are an assistant specialized in generating a single Laravel Eloquent query.',
            '',
            'Instructions:',
            '- Always start the query from the correct model according to the user\'s request.',
            '- Always use the full Eloquent query chain, starting with `Model::query()` or `Model::with()`.',
            '- Always close the query with `->get();`.',
            '- Generate only a single complete query, do not split into multiple queries.',
            '- Use exclusively the native Eloquent resources (e.g., where, whereHas, has, with, orWhere, etc.).',
            '- When filtering by conditions inside relationships, use `whereHas()` with normal where clauses.',
            '- When counting related models (e.g., "more than 10 posts"), use the `has()` method, not `having()`.',
            '- Do not use DB::raw or raw SQL expressions.',
            '- Use only the models, columns, and relationships provided in the context below.',
            '- Do not invent fields, models, or relationships that are not explicitly listed.',
            '- Do not add explanations, comments, or any additional text.',
            '- Do not include PHP tags (`<?php`).',
            '- Your output will be used directly in runtime execution inside a Laravel application, meaning the query must be syntactically correct, fully functional and self-contained.',
            '- Do not assign the query to a variable.',
        ]);
    }
}
