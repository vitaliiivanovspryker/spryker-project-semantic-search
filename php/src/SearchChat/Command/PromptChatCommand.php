<?php

namespace Spryker\SearchChat\Command;

use Spryker\ConfigResolverTrait;
use Spryker\Factory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Wamania\Snowball\StemmerFactory;

class PromptChatCommand extends Command
{
    use ConfigResolverTrait;

    private const EXIT = [
        'exit',
        'quit',
        'q',
        'done',
    ];

    protected function configure()
    {
        $this
            ->setName('project:search')
            ->setDescription('Ask a question and get an answer.')
            ->setHelp('This command allows you to ask a question and receive a response.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $factory = Factory::init();

        $embeddingClient = $factory->createEmbeddingsClient();
        $vectorDbClient = $factory->createChromaDbClient();
        $collectionId = $vectorDbClient->getCollectionIdByName($this->getConfig()->getProjectName());

        $helper = $this->getHelper('question');

        $output->writeln('<info>Welcome to Spryker Project semantic search SearchChat! Type "exit" to quit.</info>');

        while (true) {
            $question = new Question('<info>Please enter your question: </info>');
            $userQuestion = $helper->ask($input, $output, $question);

            if (in_array(strtolower(trim($userQuestion)), static::EXIT)) {
                $output->writeln('<info>Goodbye!</info>');
                break;
            }

            $output->writeln("<comment>Your question is: $userQuestion. Searching...</comment>");
            $normalizedQuestion = strtolower(trim($userQuestion));

            if (!$normalizedQuestion) {
                continue;
            }

            $embeddedPrompt = $embeddingClient->getEmbeddingVector($normalizedQuestion);
            $answer = $vectorDbClient->queryByEmbedding($collectionId, $embeddedPrompt, $this->getConfig()->getMaxQueryResults());

            $output->writeln('<comment>Check next files:</comment>');

            foreach (array_chunk($answer['metadatas'][0] ?? [], $this->getConfig()->getMaxChunkDisplayResults()) as $chunkedMetadata) {
                    foreach ($chunkedMetadata as $metadatum) {
                        echo PHP_EOL;
                        echo "\n\e[1;34m======================================================================================================================================================\e[0m\n";
                        echo $metadatum['code'] . PHP_EOL;
                        echo $metadatum['file_reference'] . PHP_EOL;
                        echo "\n\e[1;34m======================================================================================================================================================\e[0m\n";
                    }

                $question = new Question('<info>Press \'ENTER\' to get more variants or any input to finish</info>' . PHP_EOL);
                $moreVariantsAnswer = $helper->ask($input, $output, $question);

                if ($moreVariantsAnswer) {
                    break;
                }
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
