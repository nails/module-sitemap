<?php

/**
 * Generates sitemap routes
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\SiteMap;

use Nails\Common\Interfaces\RouteGenerator;
use Nails\Factory;

class Routes implements RouteGenerator
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public static function generate()
    {
        $oModel = Factory::model('SiteMap', 'nailsapp/module-sitemap');
        return [
            'sitemap'                  => 'sitemap/sitemap',
            $oModel->getFilenameJson() => 'sitemap/sitemap',
            $oModel->getFilenameXml()  => 'sitemap/sitemap',
        ];
    }
}
