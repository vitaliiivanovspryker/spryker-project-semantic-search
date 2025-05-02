<?php

namespace Spryker\IndexProject\DataProvider;

use Iterator;

class FacadeDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'FacadeInterface';
    }
}
