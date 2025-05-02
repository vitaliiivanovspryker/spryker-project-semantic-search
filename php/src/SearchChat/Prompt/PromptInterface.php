<?php

namespace Spryker\SearchChat\Prompt;

interface PromptInterface
{
    public function getPrompt(array $parameters = []): string;
}
