<?php

/**
 * Generate a sitemap
 *
 * @package     Nails
 * @subpackage  module-sitemap
 * @category    Service
 * @author      Nails Dev Team
 */

namespace Nails\SiteMap\Service;

use DOMDocument;
use Nails\SiteMap\Exception\WriteException;

class SiteMap
{
    /**
     * Where to store the sitemap
     */
    const SITEMAP_DIR = NAILS_APP_PATH;

    /**
     * The name to give the sitemap file
     */
    const SITEMAP_FILE = 'sitemap.xml';

    // --------------------------------------------------------------------------

    /**
     * The various sitemap generators
     * @var array
     */
    protected static $aGenerators = [];

    // --------------------------------------------------------------------------

    /**
     * Construct SiteMap
     */
    public function __construct()
    {
        static::$aGenerators = static::getGenerators();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available generators
     * @return array
     */
    public static function getGenerators()
    {
        $aGenerators = [];
        $aComponents = array_merge(
            [(object) ['namespace' => 'App\\']],
            _NAILS_GET_MODULES()
        );

        foreach ($aComponents as $oComponent) {
            $sClass = '\\' . $oComponent->namespace . 'SiteMap\Generator';
            if (class_exists($sClass) && classImplements($sClass, 'Nails\SiteMap\Interfaces\Generator')) {
                $aGenerators[] = $sClass;
            }
        }

        return $aGenerators;
    }

    // --------------------------------------------------------------------------

    /**
     * Writes the sitemap file
     * @throws WriteException
     */
    public function write()
    {
        //  Begin XML
        $oXmlObject = new DOMDocument('1.0', 'UTF-8');

        $oUrlSet = $oXmlObject->createElement('urlset');
        $oUrlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $oUrlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $oUrlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        foreach (static::$aGenerators as $sGeneratorClass) {

            $oGenerator = new $sGeneratorClass();
            $aItems     = $oGenerator->execute();

            foreach ($aItems as $oItem) {

                $sUrl = $oItem->getUrl();
                if (empty($sUrl)) {
                    continue;
                }

                $oUrl = $oXmlObject->createElement('url');
                $oUrl->appendChild($oXmlObject->createElement('loc', $sUrl));
                $oUrl->appendChild($oXmlObject->createElement('lastmod', $oItem->getModified()));
                $oUrl->appendChild($oXmlObject->createElement('changefreq', $oItem->getChangeFrequency()));
                $oUrl->appendChild($oXmlObject->createElement('priority', $oItem->getPriority()));

                $aAlternates = $oItem->getAlternates();
                foreach ($aAlternates as $oAlternate) {
                    $oNode = $oXmlObject->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link');
                    $oNode->setAttribute('rel', 'alternate');
                    $oNode->setAttribute('hreflang', $oAlternate->getLang());
                    $oNode->setAttribute('href', $oAlternate->getUrl());
                    $oUrl->appendChild($oNode);
                }

                $oUrlSet->appendChild($oUrl);
            }
        }

        $oXmlObject->appendChild($oUrlSet);
        $oXmlObject->formatOutput = true;
        if (!$oXmlObject->save(static::SITEMAP_DIR . static::SITEMAP_FILE)) {
            throw new WriteException('Failed to write sitemap.xml file');
        }
    }
}
