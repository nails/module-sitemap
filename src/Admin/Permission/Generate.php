<?php

namespace Nails\SiteMap\Admin\Permission;

use Nails\Admin\Interfaces\Permission;

class Generate implements Permission
{
    public function label(): string
    {
        return 'Can generate the sitemap';
    }

    public function group(): string
    {
        return 'Sitemap';
    }
}
