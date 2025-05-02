<?php

declare(strict_types=1);

namespace Spryker\AIAgent\SearchResultAssistant;

use NeuronAI\Agent;
use NeuronAI\Providers\AIProviderInterface;
use Spryker\AIAgent\AIProviderResolver;

class SearchResultAssistantAgent extends Agent implements SearchResultAssistantAgentInterface
{
    protected function provider(): AIProviderInterface
    {
        return (new AIProviderResolver())
            ->getProvider();
    }

    public function instructions(): string
    {
        return 'You are an AI Agent specialized in evaluating and summarising as a text data by relevance to user\'s prompt.'
            . 'Read search results.'
            . 'Read user\'s prompt.'
            . 'Do not edit or modify search results'
            . 'Do not generate code.'
            . 'Do not generate additional information for context.'
            . 'Answer with plaintext with no special tags.';
    }

}
