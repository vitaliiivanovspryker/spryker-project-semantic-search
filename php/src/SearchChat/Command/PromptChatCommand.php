<?php

declare(strict_types=1);

namespace Spryker\SearchChat\Command;

use NeuronAI\Chat\Messages\UserMessage;
use Spryker\ConfigResolverTrait;
use Spryker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class PromptChatCommand extends Command
{
    use ConfigResolverTrait;

    /**
     * Commands that will exit the chat
     */
    private const EXIT_COMMANDS = [
        'exit',
        'quit',
        'q',
        'end',
    ];

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('project:search')
            ->setDescription('Ask a question and get an answer.')
            ->setHelp('This command allows you to ask a question and receive a response.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureOutputStyles($output);
        $factory = Factory::init();

        $promptHelper = $factory->createPromptHelper();
        $typeDetector = $factory->createTypeDetector();
        $embeddingClient = $factory->createEmbeddingsClient();
        $vectorDbClient = $factory->createChromaDbClient();
        $collectionId = $vectorDbClient->getCollectionIdByName($this->getConfig()->getProjectName());

        $helper = $this->getHelper('question');
        $output->writeln([
            '',
            '<header>╔════════════════════════════════════════════════════════════╗</>',
            '<header>║                                                            ║</>',
            '<header>║      Welcome to Spryker Project Semantic SearchChat!       ║</>',
            '<header>║           Type "exit" "quit" "q" "end" to exit             ║</>',
            '<header>║                                                            ║</>',
            '<header>╚════════════════════════════════════════════════════════════╝</>',
            '',
        ]);

        while (true) {
            $question = new Question('<question> >> I would like to find: </question>');
            $userQuestion = $helper->ask($input, $output, $question);

            if (empty($userQuestion) || in_array(strtolower(trim($userQuestion)), self::EXIT_COMMANDS, true)) {
                $output->writeln('<info> Goodbye! Have a great day!</info>');
                break;
            }

            $output->writeln("<processing>Processing query: \"$userQuestion\"...</processing>");
            $normalizedQuestion = $promptHelper->normalisePrompts($userQuestion);

            if (empty($normalizedQuestion)) {
                $output->writeln('<error>Could not process your question. Please try again.</error>');
                continue;
            }

            $types = $typeDetector->getTypesByPrompts($normalizedQuestion);
            $filters = [];

            if (!empty($types)) {
                $output->writeln("<processing>Detected relevant filters: " . implode(', ', $types) . "</processing>");
                $filters = $this->createFilters($types);
            }

            $embeddedPrompt = $embeddingClient->getEmbeddingVector($normalizedQuestion);
            $answer = $vectorDbClient->queryByEmbedding(
                collectionId: $collectionId,
                queryEmbedding: $embeddedPrompt,
                numResults: $this->getConfig()->getMaxQueryResults(),
                filter: $filters,
            );

            $metadata = $answer['metadatas'][0] ?? [];

            if (empty($metadata)) {
                $output->writeln('<error>No results found. Please try a different query.</error>');
                continue;
            }

            $searchMode = $factory->getConfig()->getSearchMode();

            if ($searchMode === 'native-plus-ai') {
                $this->renderWithAI($factory, $normalizedQuestion, $metadata);
            } elseif ($searchMode === 'native') {
                $this->renderResults($input, $output, $metadata);
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    /**
     * @param Factory $factory
     * @param string $normalizedQuestion
     * @param array $metadata
     *
     * @return void
     */
    private function renderWithAI(Factory $factory, string $normalizedQuestion, array $metadata): void
    {
        echo PHP_EOL;
        echo "┌────────────────────────────────────────────────────────────┐" . PHP_EOL;
        echo "│ \e[1;36m Search Results \e[0m                                           │" . PHP_EOL;
        echo "└────────────────────────────────────────────────────────────┘" . PHP_EOL;

        foreach ($metadata as $index => $metadatum) {
            echo PHP_EOL;
            echo "\e[1;33m" . ($index + 1) . ". " . $metadatum['name'] . "\e[0m" . PHP_EOL;
            echo "\e[0;90m" . $metadatum['file_reference'] . "\e[0m" . PHP_EOL;
        }

        $preparedData = json_encode(
            array_column($metadata, 'code'),
            JSON_PRETTY_PRINT,
        );

        echo PHP_EOL;
        echo "┌────────────────────────────────────────────────────────────┐" . PHP_EOL;
        echo "│ \e[1;36mAI Analysis Results...\e[0m                                     │" . PHP_EOL;
        echo "└────────────────────────────────────────────────────────────┘" . PHP_EOL;

        $prompt =  'Considering search results' . PHP_EOL . sprintf('```json' . PHP_EOL . '%s ' . PHP_EOL . '```'  . PHP_EOL, $preparedData)
            . 'answer to user\'s search query ' . PHP_EOL . '```text' . PHP_EOL . $normalizedQuestion. PHP_EOL .  '```' . PHP_EOL .
            'Summarise results, sort them by relevance to user search query, list sorted by relevance results (at least 30%). Print results as for console' . PHP_EOL;

        $response = $factory
            ->createSearchResultAssistantAgent()
            ->stream(
                new UserMessage($prompt)
            );

        echo "\e[1;36m";
        foreach ($response as $string) {
            echo $string;
        }
        echo "\e[0m";
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param array $metadata
     *
     * @return void
     */
    private function renderResults(InputInterface $input, OutputInterface $output, array $metadata): void
    {
        $helper = $this->getHelper('question');
        $chunks = array_chunk($metadata, $this->getConfig()->getMaxChunkDisplayResults());

        foreach ($chunks as $index => $chunkedMetadata) {
            foreach ($chunkedMetadata as $resultIndex => $metadatum) {
                $overallIndex = ($index * $this->getConfig()->getMaxChunkDisplayResults()) + $resultIndex + 1;
                echo "┌────────────────────────────────────────────────────────────┐" . PHP_EOL;
                echo "│ \e[1;36mResult #" . $overallIndex . "\e[0m                                                  │" . PHP_EOL;
                echo "└────────────────────────────────────────────────────────────┘" . PHP_EOL;
                echo "\e[1;33mFile:\e[0m " . $metadatum['file_reference'] . PHP_EOL;
                echo "────────────────────────────────────────────────────────────" . PHP_EOL;
                echo $metadatum['code'] . PHP_EOL;
            }

            if ($index < count($chunks) - 1) {
                $question = new Question('<question> >> Press ENTER to see more results or type anything to finish: </question>');
                $moreVariantsAnswer = $helper->ask($input, $output, $question);

                if (!empty($moreVariantsAnswer)) {
                    break;
                }
            }
        }
    }

    /**
     * @param array $types
     *
     * @return array
     */
    private function createFilters(array $types): array
    {
        if (empty($types)) {
            return [];
        }

        return [
            'type' => [
                '$in' => $types,
            ],
        ];
    }

    /**
     * @param OutputInterface $output
     *
     * @return void
     */
    private function configureOutputStyles(OutputInterface $output): void
    {
        $formatter = $output->getFormatter();

        $formatter->setStyle('header', new OutputFormatterStyle('cyan', null, ['bold']));
        $formatter->setStyle('question', new OutputFormatterStyle('green', null, ['bold']));
        $formatter->setStyle('processing', new OutputFormatterStyle('yellow'));
        $formatter->setStyle('error', new OutputFormatterStyle('red', null, ['bold']));
    }
}
