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

namespace Nails\Routes\SiteMap;

use Nails\Common\Model\BaseRoutes;
use Nails\Factory;

class Routes extends BaseRoutes
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public function getRoutes()
    {
        $oModel = Factory::model('SiteMap', 'nailsapp/module-sitemap');
        return [
            'sitemap'                  => 'sitemap/sitemap',
            $oModel->getFilenameJson() => 'sitemap/sitemap',
            $oModel->getFilenameXml()  => 'sitemap/sitemap',
        ];
    }
}
