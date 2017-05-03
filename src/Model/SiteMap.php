<?php

/**
 * This class provides some common admin controller functionality
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Model
 * @author      Nails Dev Team
 * @todo        bundle generators into their respective modules (like routes or Admin Data Exports)
 */

namespace Nails\SiteMap\Model;

use Nails\Factory;
use Nails\Common\Model\Base;

class SiteMap extends Base
{
    protected $aWriters;
    protected $sFilenameJson;
    protected $sFilenameXml;

    // --------------------------------------------------------------------------

    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  Set Defaults
        $this->aWriters      = [];
        $this->sFilenameJson = 'sitemap.json';
        $this->sFilenameXml  = 'sitemap.xml';

        /**
         * Default writers
         * @todo: make these powered by the modules, ie. not in this file
         */
        $this->aWriters['static'] = [$this, 'generatorStatic'];
        $this->aWriters['cms']    = [$this, 'generatorCms'];
        $this->aWriters['blog']   = [$this, 'generatorBlog'];
        $this->aWriters['shop']   = [$this, 'generatorShop'];
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the JSON filename
     * @return string
     */
    public function getFilenameJson()
    {
        return $this->sFilenameJson;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the XML filename
     * @return string
     */
    public function getFilenameXml()
    {
        return $this->sFilenameXml;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates the various site maps
     * @return boolean
     */
    public function generate()
    {
        //  Will we be able to write to the cache?
        if (!is_writable(DEPLOY_CACHE_DIR)) {
            $this->setError('Cache is not writable.');
            return false;
        }

        // --------------------------------------------------------------------------

        $oNow = Factory::factory('DateTime');
        $oMap = (object) [
            'meta'  => (object) [
                'generated' => $oNow->format(DATE_ATOM),
            ],
            'pages' => [],
        ];

        foreach ($this->aWriters as $sSlug => $aMethod) {
            if (is_callable([$aMethod[0], $aMethod[1]])) {
                $result = call_user_func([$aMethod[0], $aMethod[1]]);
                if (is_array($result)) {
                    $oMap->pages = array_merge($oMap->pages, $result);
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Sort the array into a vaguely sensible order
        Factory::helper('array');
        array_sort_multi($oMap->pages, 'location');
        $oMap->pages = array_values($oMap->pages);

        // --------------------------------------------------------------------------

        //  Save this data as JSON and XML files
        Factory::helper('file');

        //  Write JSON sitemap
        if (!$this->writeJson($oMap)) {
            return false;
        }

        //  Write XML sitemap
        if (!$this->writeXml($oMap)) {
            return false;
        }

        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for static pages
     * @return array
     */
    protected function generatorStatic()
    {
        return [
            (object) [
                'title'       => 'Homepage',
                'location'    => site_url(),
                'breadcrumbs' => '',
                'changefreq'  => 'daily',
                'priority'    => 1,
            ],
        ];
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for CMS Pages
     * @return array
     */
    protected function generatorCms()
    {
        $aMap = [];

        if (isModuleEnabled('nailsapp/module-cms')) {

            $oPageModel  = Factory::model('Page', 'nailsapp/module-cms');
            $aPages      = $oPageModel->getAll();
            $iCounter    = 0;
            $iHomepageId = $oPageModel->getHomepageId();

            foreach ($aPages as $oPage) {
                if ($oPage->is_published && $oPage->id != $iHomepageId) {
                    $aMap[$iCounter] = (object) [
                        'title'       => htmlentities($oPage->published->title),
                        'breadcrumbs' => $oPage->published->breadcrumbs,
                        'location'    => site_url($oPage->published->slug),
                        'lastmod'     => date(DATE_ATOM, strtotime($oPage->modified)),
                        'changefreq'  => 'monthly',
                        'priority'    => 0.5,
                    ];
                }
                $iCounter++;
            }
        }

        return $aMap;
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for Blog posts
     * @return array
     */
    protected function generatorBlog()
    {
        $aMap = [];

        if (isModuleEnabled('nailsapp/module-blog')) {

            $oBlogModel = Factory::model('Blog', 'nailsapp/module-blog');
            $oPostModel = Factory::model('Post', 'nailsapp/module-blog');

            $aBlogs = $oBlogModel->getAll();

            foreach ($aBlogs as $oBlog) {

                //  Only published items which are not scheduled for the future
                $iCounter = 0;
                $aPosts   = $oPostModel->getAll([
                    'where' => [
                        ['blog_id', $oBlog->id],
                        ['is_published', true],
                        ['published <=', 'NOW()', false],
                    ],
                ]);

                //  Blog front page route
                $aMap[$iCounter] = (object) [
                    'title'      => htmlentities($oBlog->label),
                    'location'   => $oBlogModel->getBlogUrl($oBlog->id),
                    'changefreq' => 'daily',
                    'priority'   => 0.5,
                ];

                $iCounter++;

                foreach ($aPosts as $oPost) {
                    $aMap[$iCounter] = (object) [
                        'title'      => htmlentities($oPost->title),
                        'location'   => $oPost->url,
                        'lastmod'    => date(DATE_ATOM, strtotime($oPost->modified)),
                        'changefreq' => 'monthly',
                        'priority'   => 0.5,
                    ];
                    $iCounter++;
                }
            }
        }

        return $aMap;
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for Shop items
     * @return array
     */
    protected function generatorShop()
    {
        $aMap = [];

        if (isModuleEnabled('nailsapp/module-shop')) {
            // @TODO: all shop product/category/tag/sale routes etc
        }

        return $aMap;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates the JSON sitemap
     *
     * @param  \stdClass $oMap The sitemap data
     *
     * @return boolean
     */
    protected function writeJson($oMap)
    {
        if (!@write_file(DEPLOY_CACHE_DIR . $this->sFilenameJson, json_encode($oMap))) {
            $this->setError('Failed to write ' . $this->sFilenameJson . '.');
            return false;
        }
        return true;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates the XML sitemap
     *
     * @param  \stdClass $oMap The sitemap data
     *
     * @return boolean
     */
    protected function writeXml($oMap)
    {
        $oFh = fopen(DEPLOY_CACHE_DIR . $this->sFilenameXml, 'w');

        if (!$oFh) {
            $this->setError('Failed to write ' . $this->sFilenameXml . ': Could not open file for writing.');
            return false;
        }

        $sXml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $sXml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        if (fwrite($oFh, $sXml)) {

            for ($i = 0; $i < count($oMap->pages); $i++) {

                $sLastMod = '';
                if (!empty($oMap->pages[$i]->lastmod)) {
                    $sLastMod = '<lastmod>' . $oMap->pages[$i]->lastmod . '</lastmod>';
                }

                $sChangeFreq = '';
                if (!empty($oMap->pages[$i]->changefreq)) {
                    $sChangeFreq = '<changefreq>' . $oMap->pages[$i]->changefreq . '</changefreq>';
                }

                $sPriority = '';
                if (!empty($oMap->pages[$i]->priority)) {
                    $sPriority = '<priority>' . $oMap->pages[$i]->priority . '</priority>';
                }

                $aXml   = [];
                $aXml[] = '<url>';
                $aXml[] .= '<loc>' . $oMap->pages[$i]->location . '</loc>';
                $aXml[] .= $sLastMod;
                $aXml[] .= $sChangeFreq;
                $aXml[] .= $sPriority;
                $aXml[] .= '</url>';

                if (!fwrite($oFh, implode("\n", $aXml))) {
                    @unlink(DEPLOY_CACHE_DIR . $this->sFilenameXml);
                    $this->setError('Failed to write ' . $this->sFilenameXml . ': Could write to file - #2.');
                    return false;
                }
            }

            //  finally, close <urlset>
            $sXml = '</urlset>' . "\n";

            if (!fwrite($oFh, $sXml)) {
                @unlink(DEPLOY_CACHE_DIR . $this->sFilenameXml);
                $this->setError('Failed to write ' . $this->sFilenameXml . ': Could write to file - #3.');
                return false;
            }

            return true;

        } else {
            @unlink(DEPLOY_CACHE_DIR . $this->sFilenameXml);
            $this->setError('Failed to write ' . $this->sFilenameXml . ': Could write to file - #1.');
            return false;
        }
    }
}
