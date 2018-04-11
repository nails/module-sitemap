<?php

namespace Nails\SiteMap\Interfaces;

interface Generator
{
    /**
     * Returns an array of \Nails\SiteMap\Factory\Url objects
     * @return array
     */
    public function execute();
}
