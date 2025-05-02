<?php

namespace Spryker\IndexProject\DataProvider;

use Iterator;

class ClientDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'ClientInterface';
    }
}
