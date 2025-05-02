<?php

namespace Spryker;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Spryker\AIProviders\AIAdapterInterface;
use Spryker\AIProviders\OllamaClient;
use Spryker\EmbeddingsClient\EmbeddingsClient;
use Spryker\EmbeddingsClient\EmbeddingsClientInterface;
use Spryker\IndexProject\DataProvider\ClientDataProvider;
use Spryker\IndexProject\DataProvider\FacadeDataProvider;
use Spryker\IndexProject\DataProvider\Finder\ProjectPathProvider;
use Spryker\IndexProject\DataProvider\Finder\ProjectPathProviderInterface;
use Spryker\IndexProject\DataProvider\Finder\Strategy\ClientInterfaceFinderStrategy;
use Spryker\IndexProject\DataProvider\Finder\Strategy\FacadeInterfaceFinderStrategy;
use Spryker\IndexProject\DataProvider\Finder\Strategy\FinderStrategyInterface;
use Spryker\IndexProject\DataProvider\Finder\Strategy\ModuleConfigFinderStrategy;
use Spryker\IndexProject\DataProvider\Finder\Strategy\PluginFinderStrategy;
use Spryker\IndexProject\DataProvider\Finder\Strategy\PluginInterfaceFinderStrategy;
use Spryker\IndexProject\DataProvider\Finder\Strategy\ServiceInterfaceFinderStrategy;
use Spryker\IndexProject\DataProvider\ModuleConfigDataProvider;
use Spryker\IndexProject\DataProvider\Parser\ClassMethodParser;
use Spryker\IndexProject\DataProvider\Parser\DocCommentParser;
use Spryker\IndexProject\DataProvider\PluginDataProvider;
use Spryker\IndexProject\DataProvider\PluginInterfaceDataProvider;
use Spryker\IndexProject\DataProvider\ServiceDataProvider;
use Spryker\SearchChat\Prompt\UserInputBeforeSearchPreparationPrompt;
use Spryker\SearchChat\Prompt\UserInputBeforeSearchPreparationPromptInterface;
use Spryker\VectorDBClient\ChromaDBClient\ChromaDbClient;
use Spryker\VectorDBClient\ChromaDBClient\ChromaDbConfig;

class Factory
{
    use ConfigResolverTrait;

    public static function init(): self
    {
        static $factory = null;

        if ($factory === null) {
            $factory = new self();
        }

        return $factory;
    }

    public function createEmbeddingsClient(): EmbeddingsClientInterface
    {
        return new EmbeddingsClient(
            $this->createHttpClient($this->getConfig()->getEmbeddingApi()),
            $this->getConfig()->getEmbeddingModel(),
        );
    }

    public function createAIAdapter(): AIAdapterInterface
    {
        return new OllamaClient(
            $this->createHttpClient($this->getConfig()->getOllamaApi()),
            $this->getConfig()->getOllamaModel(),
        );
    }

    public function createChromaDbClient(): ChromaDbClient
    {
        return new ChromaDbClient(
            new Client(),
            new ChromaDbConfig($this->getConfig()->getChromaDbApi()),
        );
    }

    public function createClassMethodExtractor(): ClassMethodParser
    {
        return new ClassMethodParser(
            (new ParserFactory)->createForHostVersion(),
            new Standard(),
            new DocCommentParser(),
        );
    }

    /**
     * @return array<DataProviderInterface>
     */
    public function getDataProviders(): array
    {
        return [
            new FacadeDataProvider(
                $this->createProjectPathProvider(new FacadeInterfaceFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
            new ClientDataProvider(
                $this->createProjectPathProvider(new ClientInterfaceFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
            new ServiceDataProvider(
                $this->createProjectPathProvider(new ServiceInterfaceFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
            new ModuleConfigDataProvider(
                $this->createProjectPathProvider(new ModuleConfigFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
            new PluginDataProvider(
                $this->createProjectPathProvider(new PluginFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
            new PluginInterfaceDataProvider(
                $this->createProjectPathProvider(new PluginInterfaceFinderStrategy()),
                $this->createClassMethodExtractor(),
                $this->getConfig(),
            ),
        ];
    }

    public function createUserInputBeforeSearchPreparationPrompt(): UserInputBeforeSearchPreparationPromptInterface
    {
        return new UserInputBeforeSearchPreparationPrompt();
    }

    protected function createProjectPathProvider(FinderStrategyInterface $finderStrategy): ProjectPathProviderInterface
    {
        return new ProjectPathProvider(
            $this->getConfig(),
            $finderStrategy,
        );
    }

    /**
     * Create an HTTP client configured for the embeddings API.
     *
     * @param string $baseUrl The base URL for the embeddings API
     *
     * @return ClientInterface The configured HTTP client
     */
    private function createHttpClient(string $baseUrl): ClientInterface
    {
        return new Client([
            'base_uri' => $baseUrl,
        ]);
    }
}
