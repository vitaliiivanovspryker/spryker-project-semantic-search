<?php

declare(strict_types=1);

namespace Spryker\AIProviders;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;

class OllamaClient implements OllamaClientInterface
{
    public function __construct(
        private ClientInterface $httpClient,
        private string $model,
    )
    {
    }

    public function ask(string $prompt): string
    {
        try {
            $response = $this->httpClient->request('POST', '/api/generate', [
                RequestOptions::JSON => [
                    'model' => $this->model,
                    'prompt' => $prompt,
                    'stream' => false,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            return $data['response'] ?? '';
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to fetch response from Ollama.');
        }
    }
}

