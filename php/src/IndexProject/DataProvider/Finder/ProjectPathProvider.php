<?php

namespace Spryker\IndexProject\DataProvider\Finder;

use Spryker\Config;
use Spryker\IndexProject\DataProvider\Finder\Strategy\FinderStrategyInterface;
use Iterator;

/**
 * Provides project paths for searching
 */
class ProjectPathProvider implements ProjectPathProviderInterface
{
    /**
     * @param \Spryker\Config $config
     */
    public function __construct(
        private Config $config,
        private FinderStrategyInterface $finderStrategy,
    ) {
    }

    /**
     * @return \Iterator<string>
     */
    public function getProjectPaths(): Iterator
    {
        return $this->finderStrategy->find($this->getDirs());
    }

    /**
     * @return array<string>
     */
    private function getDirs(): array
    {
        return [
            PROJECT_DIR . $this->config->getProjectSrcDir(),
            PROJECT_DIR . '/vendor/spryker/spryker',
            PROJECT_DIR . '/vendor/spryker/spryker-shop',
            PROJECT_DIR . '/vendor/spryker-eco',
        ];
    }
}
