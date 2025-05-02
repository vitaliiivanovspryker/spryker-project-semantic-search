<?php

namespace Spryker\SearchChat\Prompt;

class UserInputBeforeSearchPreparationPrompt implements UserInputBeforeSearchPreparationPromptInterface
{
    private const PROMT_TEMPLATE = '
You are an AI assistant that performs semantic search in a Spryker project.

Convert user\'s prompt "%s" to make for applicable for searching in PHP Spryker project.

Response with new user\'s prompt.
No additional text and your comments

';

    public function getPrompt(array $parameters = []): string
    {
        [$userPrompt] = $parameters;

        $generatedPrompt = sprintf(self::PROMT_TEMPLATE, $userPrompt);

        return $generatedPrompt;
    }
}
