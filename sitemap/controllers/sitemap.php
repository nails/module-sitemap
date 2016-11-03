<?php

/**
 * This class is the sitemap controller. It produces the various site maps supported by Nails.
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Controller
 * @author      Nails Dev Team
 * @link
 */

use Nails\Factory;
use App\Controller\Base;

class NAILS_Sitemap extends Base
{
    protected $filenameJson;
    protected $filenameXml;

    // --------------------------------------------------------------------------

    /**
     * Construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        // --------------------------------------------------------------------------

        $this->load->model('sitemap/sitemap_model');

        $this->filenameJson = $this->sitemap_model->getFilenameJson();
        $this->filenameXml  = $this->sitemap_model->getFilenameXml();
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

            case $this->filenameXml:

                $this->outputXml();
                break;

            case $this->filenameJson:

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
        //  Check cache for $this->filenameJson
        if (!$this->checkCache($this->filenameJson)) {

            return;
        }

        // --------------------------------------------------------------------------

        $this->data['sitemap'] = json_decode(file_get_contents(DEPLOY_CACHE_DIR . $this->filenameJson));

        if (empty($this->data['sitemap'])) {

            /**
             * Something fishy goin' on.
             * Send a temporarily unavailable header, we don't want search engines
             * unlisting us because of this.
             */

            $this->output->set_header($this->input->server('SERVER_PROTOCOL') . ' 503 Service Temporarily Unavailable');
            $this->output->set_header('Status: 503 Service Temporarily Unavailable');
            $this->output->set_header('Retry-After: 7200');

            //  Inform devs
            $subject = $this->filenameJson . ' contained no data';
            $message = 'The cache file for the site map was found but did not contain any data.';
            sendDeveloperMail($subject, $message);

            $this->load->view('sitemap/error');
            return false;
        }

        // --------------------------------------------------------------------------

        //  Page data
        $this->data['page']->title = 'Site Map';

        // --------------------------------------------------------------------------

        $this->load->view('structure/header', $this->data);
        $this->load->view('sitemap/html', $this->data);
        $this->load->view('structure/footer', $this->data);
    }

    // --------------------------------------------------------------------------

    /**
     * Generates a XML, machine friendly sitemap
     * @return void
     */
    protected function outputXml()
    {
        //  Check cache for $this->filenameXml
        if (!$this->checkCache($this->filenameXml)) {

            return;
        }

        // --------------------------------------------------------------------------

        //  Set XML headers
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: text/xml');
        header('Pragma: no-cache');

        readfile(DEPLOY_CACHE_DIR . $this->filenameXml);

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
     * Generates a JSON, machine friendly sitemap
     * @return void
     */
    protected function outputJson()
    {
        //  Check cache for $this->filenameJson
        if (!$this->checkCache($this->filenameJson)) {

            return;
        }

        // --------------------------------------------------------------------------

        //  Set JSON headers
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Content-type: application/json');
        header('Pragma: no-cache');

        //  Stream
        readfile(DEPLOY_CACHE_DIR . $this->filenameJson);

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
     * @param  string $file The cache file to check
     * @return boolean
     */
    protected function checkCache($file)
    {
        //  Check cache for $file
        if (!is_file(DEPLOY_CACHE_DIR . $file)) {

            //  If not found, generate
            $this->load->model('sitemap/sitemap_model');

            if (!$this->sitemap_model->generate()) {

                //  Failed to generate sitemap
                $oLogger = Factory::service('Logger');
                $oLogger->line('Failed to generate sitemap: ' . $this->sitemap_model->lastError());

                //  Let the dev's know too, this could be serious
                $subject = 'Failed to generate sitemap';
                $message = 'There was no ' . $file . ' data in the cache and I failed to recreate it.';
                sendDeveloperMail($subject, $message);

                //  Send a temporarily unavailable header, we don't want search engines unlisting us because of this.
                $protocol = $this->input->server('SERVER_PROTOCOL');
                $this->output->set_header($protocol . ' 503 Service Temporarily Unavailable');
                $this->output->set_header('Status: 503 Service Temporarily Unavailable');
                $this->output->set_header('Retry-After: 7200');

                $this->load->view('sitemap/error');
                return false;
            }
        }

        return true;
    }
}

// --------------------------------------------------------------------------

/**
 * OVERLOADING NAILS' EMAIL MODULES
 *
 * The following block of code makes it simple to extend one of the core Nails
 * controllers. Some might argue it's a little hacky but it's a simple 'fix'
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
 * If/when we want to extend the main class we simply define NAILS_ALLOW_EXTENSION_CLASSNAME
 * before including this PHP file and extend as normal (i.e in the same way as below);
 * the helper won't be declared so we can declare our own one, app specific.
 *
 **/

if (!defined('NAILS_ALLOW_EXTENSION_SITEMAP')) {

    class Sitemap extends NAILS_Sitemap
    {
    }
}
