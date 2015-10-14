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

namespace Nails\Routes\Sitemap;

class Routes
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public function getRoutes()
    {
        get_instance()->load->model('sitemap/sitemap_model');

        $routes = array();
        $routes = $routes + get_instance()->sitemap_model->getRoutes();

        return $routes;
    }
}
