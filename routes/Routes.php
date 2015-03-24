<?php

namespace Nails\Routes\Sitemap;

/**
 * Generates sitemap routes
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

class Routes
{
    /**
     * Returns an array of routes for this module
     * @return array
     */
    public function getRoutes()
    {
        $this->load->model('sitemap/sitemap_model');

        $routes = array();
        $routes = $routes + $this->sitemap_model->getRoutes();

        return $routes;
    }
}
