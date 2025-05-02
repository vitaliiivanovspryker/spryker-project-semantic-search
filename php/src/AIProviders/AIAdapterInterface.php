<?php

declare(strict_types=1);

namespace Spryker\AIProviders;

interface AIAdapterInterface
{
    public function ask(string $prompt): string;
}
