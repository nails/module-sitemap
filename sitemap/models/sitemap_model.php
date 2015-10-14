<?php

/**
 * This class provides some common admin controller functionality
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Model
 * @author      Nails Dev Team
 * @link
 */

use Nails\Factory;

class NAILS_Sitemap_model extends NAILS_Model
{
    protected $writers;
    protected $filenameJson;
    protected $filenameXml;

    // --------------------------------------------------------------------------

    /**
     * Construct the model
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        //  Set Defaults
        $this->writers      = array();
        $this->filenameJson = 'sitemap.json';
        $this->filenameXml  = 'sitemap.xml';

        /**
         * Default writers
         * @todo: make these powered by the modules, ie. not in this file
         */
        $this->writers['static'] = array($this, 'generatorStatic');
        $this->writers['cms']    = array($this, 'generatorCms');
        $this->writers['blog']   = array($this, 'generatorBlog');
        $this->writers['shop']   = array($this, 'generatorShop');
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the JSON filename
     * @return string
     */
    public function getFilenameJson()
    {
        return $this->filenameJson;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the XML filename
     * @return string
     */
    public function getFilenameXml()
    {
        return $this->filenameXml;
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

            $this->_set_error('Cache is not writeable.');
            return false;
        }

        // --------------------------------------------------------------------------

        $map                  = new stdClass();
        $map->meta            = new stdClass();
        $map->meta->generated = date(DATE_ATOM);
        $map->pages           = array();

        foreach ($this->writers as $slug => $method) {

            if (is_callable(array($method[0], $method[1]))) {

                $result = call_user_func(array($method[0], $method[1]));

                if (is_array($result)) {

                    $map->pages = array_merge($map->pages, $result);
                }
            }
        }

        // --------------------------------------------------------------------------

        //  Sort the array into a vaguely sensible order
        Factory::helper('array');
        array_sort_multi($map->pages, 'location');

        // --------------------------------------------------------------------------

        //  Save this data as JSON and XML files
        Factory::helper('file');

        //  Write JSON sitemap
        if (!$this->writeJson($map)) {

            return false;
        }

        //  Write XML sitemap
        if (!$this->writeXml($map)) {

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
        $map = array();

        // --------------------------------------------------------------------------

        $map[0]              = new stdClass();
        $map[0]->title       = 'Homepage';
        $map[0]->location    = site_url();
        $map[0]->breadcrumbs = '';
        $map[0]->changefreq  = 'daily';
        $map[0]->priority    = 1;

        // --------------------------------------------------------------------------

        return $map;
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for CMS Pages
     * @return array
     */
    protected function generatorCms()
    {
        if (isModuleEnabled('nailsapp/module-cms')) {

            $map = array();

            $this->load->model('cms/cms_page_model');

            $pages   = $this->cms_page_model->get_all();
            $counter = 0;

            foreach ($pages as $page) {

                if ($page->is_published && !$page->is_homepage) {

                    $map[$counter]              = new stdClass();
                    $map[$counter]->title       = htmlentities($page->published->title);
                    $map[$counter]->breadcrumbs = $page->published->breadcrumbs;
                    $map[$counter]->location    = site_url($page->published->slug);
                    $map[$counter]->lastmod     = date(DATE_ATOM, strtotime($page->modified));
                    $map[$counter]->changefreq  = 'monthly';
                    $map[$counter]->priority    = 0.5;
                }

                $counter++;
            }

            return $map;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for Blog posts
     * @return array
     */
    protected function generatorBlog()
    {
        if (isModuleEnabled('nailsapp/module-blog')) {

            $map = array();

            $this->load->model('blog/blog_model');
            $this->load->model('blog/blog_post_model');

            $blogs = $this->blog_model->get_all();

            foreach ($blogs as $blog) {

                //  Only published items which are not schduled for the future
                $data            = array();
                $data['where']   = array();
                $data['where'][] = array('column' => 'blog_id',      'value' => $blog->id);
                $data['where'][] = array('column' => 'is_published', 'value' => true);
                $data['where'][] = array('column' => 'published <=', 'value' => 'NOW()', 'escape' => false);

                $posts   = $this->blog_post_model->get_all(null, null, $data);
                $counter = 0;

                //  Blog front page route
                $map[$counter]             = new stdClass();
                $map[$counter]->title      = htmlentities($blog->label);
                $map[$counter]->location   = $this->blog_model->getBlogUrl($blog->id);
                $map[$counter]->changefreq = 'daily';
                $map[$counter]->priority   = 0.5;

                $counter++;

                foreach ($posts as $post) {

                    $map[$counter]             = new stdClass();
                    $map[$counter]->title      = htmlentities($post->title);
                    $map[$counter]->location   = $post->url;
                    $map[$counter]->lastmod    = date(DATE_ATOM, strtotime($post->modified));
                    $map[$counter]->changefreq = 'monthly';
                    $map[$counter]->priority   = 0.5;

                    $counter++;
                }
            }

            return $map;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generate site map entries for Shop items
     * @return array
     */
    protected function generatorShop()
    {
        if (isModuleEnabled('nailsapp/module-shop')) {

            // @TODO: all shop product/category/tag/sale routes etc
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Get all the routes where the sitemap exists
     *
     * @return array
     */
    public function getRoutes()
    {
        $routes                      = array();
        $routes['sitemap']           = 'sitemap/sitemap';
        $routes[$this->filenameXml]  = 'sitemap/sitemap';
        $routes[$this->filenameJson] = 'sitemap/sitemap';

        return $routes;
    }

    // --------------------------------------------------------------------------

    /**
     * Generates the JSON sitemap
     * @param  stdClass $map The sitemap data
     * @return boolean
     */
    protected function writeJson($map)
    {
        if (!@write_file(DEPLOY_CACHE_DIR . $this->filenameJson, json_encode($map))) {

            $this->_set_error('Failed to write ' . $this->filenameJson . '.');
            return false;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates the XML sitemap
     * @param  stdClass $map The sitemap data
     * @return boolean
     */
    protected function writeXml($map)
    {
        $fh = fopen(DEPLOY_CACHE_DIR . $this->filenameXml, 'w');

        if (!$fh) {

            $this->_set_error('Failed to write ' . $this->filenameXml . ': Could not open file for writing.');
            return false;
        }

        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'. "\n";

        if (fwrite($fh, $xml)) {

            for ($i = 0; $i < count($map->pages); $i++) {

                $xml  = '<url>' . "\n";
                $xml .= '<loc>' . $map->pages[$i]->location . '</loc>'. "\n";
                $xml .= !empty($map->pages[$i]->lastmod)    ? '<lastmod>' . $map->pages[$i]->lastmod . '</lastmod>' . "\n"         : '';
                $xml .= !empty($map->pages[$i]->changefreq) ? '<changefreq>' . $map->pages[$i]->changefreq. '</changefreq>' . "\n" : '';
                $xml .= !empty($map->pages[$i]->priority)   ? '<priority>' . $map->pages[$i]->priority. '</priority>' . "\n"       : '';
                $xml .= '</url>'. "\n";

                if (!fwrite($fh, $xml)) {

                    @unlink(DEPLOY_CACHE_DIR . $this->filenameXml);
                    $this->_set_error('Failed to write ' . $this->filenameXml . ': Could write to file - #2.');
                    return false;
                }
            }

            //  finally, close <urlset>
            $xml = '</urlset>' . "\n";

            if (!fwrite($fh, $xml)) {

                @unlink(DEPLOY_CACHE_DIR . $this->filenameXml);
                $this->_set_error('Failed to write ' . $this->filenameXml . ': Could write to file - #3.');
                return false;
            }

            return true;

        } else {

            @unlink(DEPLOY_CACHE_DIR . $this->filenameXml);
            $this->_set_error('Failed to write ' . $this->filenameXml . ': Could write to file - #1.');
            return false;
        }
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' MODELS
 *
 * The following block of code makes it simple to extend one of the core
 * models. Some might argue it's a little hacky but it's a simple 'fix'
 * which negates the need to massively extend the CodeIgniter Loader class
 * even further (in all honesty I just can't face understanding the whole
 * Loader class well enough to change it 'properly').
 *
 * Here's how it works:
 *
 * CodeIgniter instantiate a class with the same name as the file, therefore
 * when we try to extend the parent class we get 'cannot redeclare class X' errors
 * and if we call our overloading class something else it will never get instantiated.
 *
 * We solve this by prefixing the main class with NAILS_ and then conditionally
 * declaring this helper class below; the helper gets instantiated et voila.
 *
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_SITEMAP_MODEL')) {

    class Sitemap_model extends NAILS_Sitemap_model
    {
    }
}
