<?php

/**
 * This class is the SiteMap controller. It produces the various site maps supported by Nails.
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Factory;
use App\Controller\Base;

class Sitemap extends Base
{
    protected $sFilenameJson;
    protected $sFilenameXml;

    // --------------------------------------------------------------------------

    /**
     * Construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        $oModel = Factory::model('SiteMap', 'nailsapp/module-sitemap');

        $this->sFilenameJson = $oModel->getFilenameJson();
        $this->sFilenameXml  = $oModel->getFilenameXml();
    }

    // --------------------------------------------------------------------------

    /**
     * Map all requests to _sitemap
     * @return  void
     **/
    public function _remap()
    {
        switch (uri_string()) {

            case 'sitemap':
                $this->outputHtml();
                break;

            case $this->sFilenameXml:
                $this->outputXml();
                break;

            case $this->sFilenameJson:
                $this->outputJson();
                break;

            default:
                show_404();
                break;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a HTML, human friendly sitemap
     * @return void
     */
    protected function outputHtml()
    {
        //  Check cache for $this->sFilenameJson
        if (!$this->checkCache($this->sFilenameJson)) {
            return;
        }

        $oView = Factory::service('View');

        $this->data['sitemap'] = json_decode(file_get_contents(CACHE_PATH . $this->sFilenameJson));

        if (empty($this->data['sitemap'])) {

            /**
             * Something fishy goin' on.
             * Send a temporarily unavailable header, we don't want search engines
             * unlisting us because of this.
             */

            $oOutput = Factory::service('Output');
            $oInput  = Factory::service('Input');
            $oOutput->set_header($oInput->server('SERVER_PROTOCOL') . ' 503 Service Temporarily Unavailable');
            $oOutput->set_header('Status: 503 Service Temporarily Unavailable');
            $oOutput->set_header('Retry-After: 7200');

            //  Inform devs
            $sSubject = $this->sFilenameJson . ' contained no data';
            $sMessage = 'The cache file for the site map was found but did not contain any data.';
            sendDeveloperMail($sSubject, $sMessage);

            $oView->load('sitemap/error');
            return false;
        }

        // --------------------------------------------------------------------------

        //  Page data
        $this->data['page']->title = 'Site Map';

        // --------------------------------------------------------------------------

        $oView->load('structure/header', $this->data);
        $oView->load('sitemap/html', $this->data);
        $oView->load('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a XML, machine friendly sitemap
     * @return void
     */
    protected function outputXml()
    {
        $this->output($this->sFilenameXml, 'text/xml');
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a JSON, machine friendly sitemap
     * @return void
     */
    protected function outputJson()
    {
        $this->output($this->sFilenameJson, 'application/json');
    }

    // --------------------------------------------------------------------------

    /**
     * Reads and outputs a file to the browser with no-cache headers
     *
     * @param string $sFilename The filename to read
     * @param string $sMime     The content type to send
     */
    protected function output($sFilename, $sMime)
    {
        //  Check cache for $this->sFilenameJson
        if (!$this->checkCache($sFilename)) {
            return;
        }

        // --------------------------------------------------------------------------

        //  Set JSON headers
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: ' . $sMime);
        header('Pragma: no-cache');

        //  Stream
        readfile(CACHE_PATH . $sFilename);

        // --------------------------------------------------------------------------

        /**
         * Kill script, th, th, that's all folks.
         * Stop the output class from hijacking our headers and setting an incorrect
         * Content-Type
         */

        exit(0);
    }

    // --------------------------------------------------------------------------

    /**
     * Checks for a cached file, if not found attempts to generate it
     *
     * @param  string $file The cache file to check
     *
     * @return boolean
     */
    protected function checkCache($file)
    {
        //  Check cache for $file
        if (!is_file(CACHE_PATH . $file)) {

            //  If not found, generate
            $oModel = Factory::model('SiteMap', 'nailsapp/module-sitemap');

            if (!$oModel->generate()) {

                //  Failed to generate sitemap
                $oLogger = Factory::service('Logger');
                $oLogger->line('Failed to generate sitemap: ' . $oModel->lastError());

                //  Let the dev's know too, this could be serious
                $sSubject = 'Failed to generate sitemap';
                $sMessage = 'There was no ' . $file . ' data in the cache and I failed to recreate it.';
                sendDeveloperMail($sSubject, $sMessage);

                //  Send a temporarily unavailable header, we don't want search engines unlisting us because of this.
                $oOutput = Factory::service('Output');
                $oInput  = Factory::service('Input');

                $oOutput->set_header($oInput->server('SERVER_PROTOCOL') . ' 503 Service Temporarily Unavailable');
                $oOutput->set_header('Status: 503 Service Temporarily Unavailable');
                $oOutput->set_header('Retry-After: 7200');

                $oView = Factory::service('View');
                $oView->load('sitemap/error');
                return false;
            }
        }

        return true;
    }
}
