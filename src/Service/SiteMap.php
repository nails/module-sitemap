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
use Nails\Common\Exception\FactoryException;
use Nails\Common\Exception\NailsException;
use Nails\Components;
use Nails\Factory;
use Nails\SiteMap\Constants;
use Nails\SiteMap\Exception\WriteException;
use Nails\SiteMap\Factory\Url;
use Nails\SiteMap\Interfaces\Generator;

/**
 * Class SiteMap
 *
 * @package Nails\SiteMap\Service
 */
class SiteMap
{
    /**
     * Where to store the sitemap
     *
     * @var string
     */
    const SITEMAP_DIR = NAILS_APP_PATH;

    /**
     * The name to give the sitemap file
     *
     * @var string
     */
    const SITEMAP_FILE = 'sitemap.xml';

    // --------------------------------------------------------------------------

    /**
     * The various sitemap generators
     *
     * @var Generator[]
     */
    protected static array $aGenerators = [];

    // --------------------------------------------------------------------------

    /**
     * SiteMap constructor.
     */
    public function __construct()
    {
        static::$aGenerators = static::getGenerators();
    }

    // --------------------------------------------------------------------------

    /**
     * Returns the available generators
     *
     * @return Generator[]
     * @throws NailsException
     */
    public static function getGenerators(): array
    {
        $aGenerators = [];
        foreach (Components::available() as $oComponent) {

            $aClasses = $oComponent
                ->findClasses('SiteMap\Generator')
                ->whichImplement(Generator::class)
                ->whichCanBeInstantiated();

            foreach ($aClasses as $sClass) {
                $aGenerators[] = $sClass;
            }
        }

        return $aGenerators;
    }

    // --------------------------------------------------------------------------

    /**
     * Writes the sitemap file
     *
     * @throws \DOMException
     * @throws FactoryException
     * @throws WriteException
     */
    public function write(string $sFile = null): void
    {
        //  Begin XML
        $oXmlObject = new DOMDocument('1.0', 'UTF-8');

        $oUrlSet = $oXmlObject->createElement('urlset');
        $oUrlSet->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $oUrlSet->setAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $oUrlSet->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        /** @var Url $oHomepage */
        $oHomepage = Factory::factory('Url', Constants::MODULE_SLUG);

        $this->addItem($oXmlObject, $oUrlSet, $oHomepage);

        foreach (static::$aGenerators as $sGeneratorClass) {

            $oGenerator = new $sGeneratorClass();
            $aItems     = $oGenerator->execute();

            foreach ($aItems as $oItem) {

                $sUrl = $oItem->getUrl();
                if (empty($sUrl)) {
                    continue;
                }

                $this->addItem($oXmlObject, $oUrlSet, $oItem);
            }
        }

        $oXmlObject->appendChild($oUrlSet);
        $oXmlObject->formatOutput = true;

        $sFile = $sFile ?? static::SITEMAP_DIR . static::SITEMAP_FILE;

        if (!$oXmlObject->save($sFile)) {
            throw new WriteException('Failed tos write to ' . $sFile);
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Add an item to the sitemap
     *
     * @param \DOMDocument $oXmlObject The Sitemap document
     * @param \DOMElement  $oUrlSet    The URLset element
     * @param Url          $oItem      The URL to add
     *
     * @return $this
     * @throws \DOMException
     */
    protected function addItem(DOMDocument $oXmlObject, \DOMElement $oUrlSet, Url $oItem): self
    {
        $oUrl = $oXmlObject->createElement('url');
        $oUrl->appendChild($oXmlObject->createElement('loc', $oItem->getUrl()));
        if ($oItem->getModified()) {
            $oUrl->appendChild($oXmlObject->createElement('lastmod', $oItem->getModified()));
        }
        $oUrl->appendChild($oXmlObject->createElement('changefreq', $oItem->getChangeFrequency()));
        $oUrl->appendChild($oXmlObject->createElement('priority', (string) $oItem->getPriority()));

        $aAlternates = $oItem->getAlternates();
        foreach ($aAlternates as $oAlternate) {
            $oNode = $oXmlObject->createElementNS('http://www.w3.org/1999/xhtml', 'xhtml:link');
            $oNode->setAttribute('rel', 'alternate');
            $oNode->setAttribute('hreflang', $oAlternate->getLang());
            $oNode->setAttribute('href', $oAlternate->getUrl());
            $oUrl->appendChild($oNode);
        }

        $oUrlSet->appendChild($oUrl);

        return $this;
    }
}
