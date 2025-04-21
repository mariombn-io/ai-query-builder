<?php

return [
    'models_path' => app_path('Models'),
    'llm' => [
        'provider' => env('IA_QUERY_BULDER_PROVIDER','ollama'),
        'ollama' => [
            'model' => env('OLLAMA_MODEL_NAME', 'llama3'),
            'base_url' => env('OLLAMA_BASE_URL','http://localhost:11434'),
        ],
    ],
    'aliases' => [],
];
