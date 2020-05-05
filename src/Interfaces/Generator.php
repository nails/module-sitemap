<?php

namespace Nails\SiteMap\Interfaces;

use Nails\SiteMap\Factory\Url;

interface Generator
{
    /**
     * Returns an array of \Nails\SiteMap\Factory\Url objects
     *
     * @return Url[]
     */
    public function execute(): array;
}
