<?php

namespace MariombnIo\IaQueryBuilder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class GenerateCacheCommand extends Command
{
    protected $signature = 'ia-query:generate-cache';

    protected $description = 'Create models cache for IA Query Builder';

    public function handle()
    {
        $modelsPath = config('ai-query.models_path', app_path('Models'));

        if (!is_dir($modelsPath)) {
            $this->error("Models path does not exist: {$modelsPath}");
            return 1;
        }

        $models = [];
        $files = File::allFiles($modelsPath);

        foreach ($files as $file) {
            $className = $this->getFullClassName($file->getPathname());

            if (!$className || !class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            if (!$reflection->isSubclassOf('Illuminate\\Database\\Eloquent\\Model')) {
                continue;
            }

            $modelInstance = app($className);

            $relations = $this->getRelations($modelInstance, $reflection);

            $models[$reflection->getShortName()] = [
                'table' => $modelInstance->getTable(),
                'columns' => $modelInstance->getFillable(),
                'relations' => $relations,
            ];
        }

        $cachePath = storage_path('app/ai-query/models-cache.json');

        File::ensureDirectoryExists(dirname($cachePath));
        File::put($cachePath, json_encode($models, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Models cache generated successfully at: ' . $cachePath);

        return 0;
    }

    protected function getFullClassName(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        if (preg_match('/namespace (.*?);/', $content, $matches)) {
            $namespace = $matches[1];
            $className = pathinfo($filePath, PATHINFO_FILENAME);

            return $namespace . '\\' . $className;
        }

        return null;
    }

    protected function getRelations($modelInstance, ReflectionClass $reflection): array
    {
        $relations = [];

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getNumberOfParameters() > 0) {
                continue;
            }

            if ($method->class !== $reflection->getName()) {
                continue;
            }

            $docComment = $method->getDocComment();

            if ($docComment && preg_match('/@return\\s+\\\\?Illuminate\\\\Database\\\\Eloquent\\\\Relations\\\\([a-zA-Z]+)/', $docComment, $matches)) {
                $relations[$method->getName()] = $matches[1];
                continue;
            }

            try {
                $result = $method->invoke($modelInstance);

                if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                    $relationType = class_basename($result);
                    $relations[$method->getName()] = $relationType;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        return $relations;
    }

}
