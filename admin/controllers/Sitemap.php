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

namespace Nails\Admin\Sitemap;

use SimpleXMLElement;
use Nails\Admin\Controller\Base;
use Nails\Admin\Helper;
use Nails\Factory;

class Sitemap extends Base
{
    /**
     * Defines the admin controller
     *
     * @return array
     */
    public static function announce()
    {
        $oNav = Factory::factory('Nav', 'nails/module-admin')
            ->setLabel('Sitemap');

        if (userHasPermission('admin:sitemap:sitemap:view')) {
            $oNav->addAction('View Sitemap');
        }

        return $oNav;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns an array of permissions which can be configured for the user
     *
     * @return array
     */
    public static function permissions()
    {
        return parent::permissions() + [
                'view'     => 'View the sitemap',
                'generate' => 'Generate the sitemap on demand',
            ];
    }

    // --------------------------------------------------------------------------

    /**
     * View the generated sitemap
     *
     * @throws \Nails\Common\Exception\FactoryException
     */
    public function index()
    {
        $oService = Factory::service('SiteMap', 'nails/module-sitemap');
        $sFile    = $oService::SITEMAP_DIR . $oService::SITEMAP_FILE;

        try {

            if (file_exists($sFile)) {
                $oXmlObject          = new SimpleXMLElement(file_get_contents($sFile));
                $this->data['aUrls'] = getFromArray('url', (array) $oXmlObject);
            }

        } catch (\Exception $e) {
            $this->data['error'] = $e->getMessage();
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
        if (!userHasPermission('admin:sitemap:sitemap:generate')) {
            show404();
        }

        $oService = Factory::service('SiteMap', 'nails/module-sitemap');
        $oService->write();

        $oSession = Factory::service('Session', 'nails/module-auth');
        $oSession->setFlashData('success', 'Sitemap generated successfully.');

        redirect('admin/sitemap/sitemap');
    }
}
