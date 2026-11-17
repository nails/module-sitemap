<?php

namespace Nails\SiteMap\Admin\Permission;

use Nails\Admin\Interfaces\Permission;

class View implements Permission
{
    public function label(): string
    {
        return 'Can view the sitemap';
    }

    public function group(): string
    {
        return 'Sitemap';
    }
}
