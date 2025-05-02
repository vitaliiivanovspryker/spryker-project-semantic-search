<?php

namespace Spryker;

trait ConfigResolverTrait
{
    public function getConfig(): Config
    {
        return Config::init();
    }
}
