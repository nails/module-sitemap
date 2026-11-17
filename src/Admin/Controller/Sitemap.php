<?php

/**
 * Manage redirects
 *
 * @package     Nails
 * @subpackage  module-redirect
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

namespace Nails\SiteMap\Admin\Controller;

use Nails\Admin\Controller\Base;
use Nails\Admin\Factory\Nav;
use Nails\Admin\Helper;
use Nails\Factory;
use Nails\SiteMap\Admin\Permission;
use Nails\SiteMap\Constants;
use SimpleXMLElement;

/**
 * Class Sitemap
 *
 * @package Nails\SiteMap\Admin\Controller
 */
class Sitemap extends Base
{
    /**
     * Defines the admin controller
     */
    public static function announce(): Nav|array|null
    {
        $oNav = Factory::factory('Nav', \Nails\Admin\Constants::MODULE_SLUG)
            ->setLabel('Sitemap');

        if (userHasPermission(Permission\View::class)) {
            $oNav->addAction('View Sitemap');
        }

        return $oNav;
    }

    // --------------------------------------------------------------------------

    /**
     * View the generated sitemap
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function index()
    {
        $oService = Factory::service('SiteMap', Constants::MODULE_SLUG);
        $sFile    = $oService::SITEMAP_DIR . $oService::SITEMAP_FILE;

        $this->data['aUrls'] = [];

        try {

            if (file_exists($sFile)) {
                $oXmlObject          = new SimpleXMLElement(file_get_contents($sFile));
                $this->data['aUrls'] = getFromArray('url', (array) $oXmlObject, []);
            } else {
                throw new \RuntimeException('Site map does not exist');
            }

        } catch (\Exception $e) {
            $this->oUserFeedback->error($e->getMessage());
        }

        $this->data['page']->title = 'Sitemap: View';
        Helper::loadView('index');
    }

    // --------------------------------------------------------------------------

    /**
     * Re-generate the sitemap
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function generate()
    {
        if (!userHasPermission(Permission\Generate::class)) {
            show404();
        }

        /** @var \Nails\SiteMap\Service\SiteMap $oService */
        $oService = Factory::service('SiteMap', Constants::MODULE_SLUG);
        $oService->write();

        $this->oUserFeedback->success('Sitemap generated successfully.');

        redirect(static::url());
    }
}
