<?php

namespace Nails\SiteMap\Factory;

use Nails\Common\Exception\NailsException;
use Nails\Factory;
use Nails\SiteMap\Constants;

/**
 * Class Url
 *
 * @package Nails\SiteMap\Factory
 */
class Url
{
    /**
     * The URL
     *
     * @var string
     */
    protected $sUrl;

    /**
     * The last modified date of the URL
     *
     * @var string
     */
    protected $sModified;

    /**
     * How often the URL is updated
     *
     * @var string
     */
    protected $sChangeFrequency = 'weekly';

    /**
     * The priority of the URL
     *
     * @var float
     */
    protected $fPriority = 0.5;

    /**
     * The language the link is in
     *
     * @var string
     */
    protected $sLang;

    /**
     * Alternative URL's
     *
     * @var Url[]
     */
    protected $aAlternates = [];

    // --------------------------------------------------------------------------

    /**
     * Sets the URL
     *
     * @param string $sUrl The URL to set
     *
     * @return $this
     */
    public function setUrl(string $sUrl): self
    {
        $this->sUrl = $sUrl;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return siteUrl($this->sUrl);
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the modified date
     *
     * @param string $sModified The modified date
     *
     * @return $this
     */
    public function setModified(string $sModified): self
    {
        $this->sModified = $sModified;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the modified date
     *
     * @return string|null
     */
    public function getModified(): ?string
    {
        return $this->sModified;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the change frequency
     *
     * @param string $sChangeFrequency the change frequency
     *
     * @return $this
     */
    public function setChangeFrequency(string $sChangeFrequency): self
    {
        $this->sChangeFrequency = $sChangeFrequency;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the change frequency
     *
     * @return string|null
     */
    public function getChangeFrequency(): ?string
    {
        return $this->sChangeFrequency;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the priority
     *
     * @param float $fPriority The priority
     *
     * @return $this
     */
    public function setPriority(float $fPriority): self
    {
        $this->fPriority = $fPriority;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the priority
     *
     * @return float|null
     */
    public function getPriority(): ?float
    {
        return $this->fPriority;
    }

    // --------------------------------------------------------------------------

    /**
     * Sets the language
     *
     * @param string $sLang The language
     *
     * @return $this
     */
    public function setLang(string $sLang): self
    {
        $this->sLang = $sLang;
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Gets the language
     *
     * @return string|null
     */
    public function getLang(): ?string
    {
        return $this->sLang;
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an alternate version of the link
     *
     * @param string $sUrl  The alternate URL
     * @param string $sLang The language of the alternate URL
     *
     * @return $this
     */
    public function setAlternate(string $sUrl, string $sLang): self
    {
        /** @var Url $oUrl */
        $oUrl = Factory::factory('Url', Constants::MODULE_SLUG);
        $oUrl
            ->setUrl($sUrl)
            ->setLang($sLang);

        $this->aAlternates[] = $oUrl;

        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any alternative URLs
     *
     * @return Url[]
     */
    public function getAlternates(): array
    {
        return $this->aAlternates;
    }
}
