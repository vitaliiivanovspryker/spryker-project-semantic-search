<?php

namespace Spryker\IndexProject\DataProvider;

use Iterator;

class ServiceDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'ServiceInterface';
    }
}
