<?php

/**
 * Migration: 0
 * Started:   16/11/2026
 */

namespace Nails\SiteMap\Database\Migration;

use Nails\Admin\Traits\Database\Migration\PermissionMap;
use Nails\Common\Interfaces;
use Nails\Common\Traits;
use Nails\SiteMap\Admin\Permission;

/**
 * Class Migration0
 *
 * Repeatable because `feature/pre-new-admin` has no equivalent migration, so an app
 * arriving from that branch resumes above this number and would never run it.
 *
 * @package Nails\SiteMap\Database\Migration
 */
class Migration0 implements Interfaces\Database\Migration\Repeatable
{
    use Traits\Database\Migration;
    use PermissionMap;

    // --------------------------------------------------------------------------

    const MAP = [
        'admin:sitemap:sitemap:view'     => Permission\View::class,
        'admin:sitemap:sitemap:generate' => Permission\Generate::class,
    ];
}
