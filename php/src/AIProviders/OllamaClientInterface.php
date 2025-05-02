<?php

namespace Spryker\AIProviders;

interface OllamaClientInterface extends AIAdapterInterface
{
    public function ask(string $prompt): string;
}
