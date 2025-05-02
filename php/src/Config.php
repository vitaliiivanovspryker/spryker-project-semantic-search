<?php

declare(strict_types=1);

namespace Spryker;

class Config
{
    public static function init(): self
    {
        static $factory = null;

        if ($factory === null) {
            $factory = new self();
        }

        return $factory;
    }

    public function getEmbeddingApi(): string
    {
        return $_ENV['EMBEDDING_API'];
    }

    public function getEmbeddingModel(): string
    {
        return $_ENV['EMBEDDING_MODEL'];
    }

    public function getChromaDbApi(): string
    {
        return $_ENV['CHROMA_DB_API'];
    }

    public function getOllamaApi(): string
    {
        return $_ENV['OLLAMA_API'];
    }

    public function getOllamaModel(): string
    {
        return $_ENV['OLLAMA_MODEL'];
    }

    public function getProjectAbsolutePath(): string
    {
        return $_ENV['PROJECT_ABSOLUTE_PATH'];
    }

    public function getProjectName(): string
    {
        return $_ENV['PROJECT_NAME'];
    }

    public function getProjectSrcDir(): string
    {
        return $_ENV['PROJECT_SRC_DIR'];
    }

    public function getMaxQueryResults(): int
    {
        return (int)$_ENV['MAX_QUERY_RESULTS'];
    }

    public function getMaxChunkDisplayResults(): int
    {
        return (int)$_ENV['MAX_CHUNK_DISPLAY_RESULTS'];
    }
}
