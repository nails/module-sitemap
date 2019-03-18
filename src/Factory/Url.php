<?php

namespace Nails\SiteMap\Factory;

use Nails\Common\Exception\NailsException;
use Nails\Factory;

class Url
{
    /**
     * Stores an array of the getter/setters for the other properties
     * @var array
     */
    protected $aMethods = [];

    /**
     * The URL
     * @var string
     */
    protected $sUrl;

    /**
     * The last modified date of the URL
     * @var string
     */
    protected $sModified;

    /**
     * How often the URL is updated
     * @var string
     */
    protected $sChangeFrequency = 'weekly';

    /**
     * The priority of the URL
     * @var float
     */
    protected $fPriority = 0.5;

    /**
     * The language the link is in
     * @var string
     */
    protected $sLang;

    /**
     * Alternative URL's
     * @var array
     */
    protected $aAlternates = [];

    // --------------------------------------------------------------------------

    /**
     * Url constructor.
     */
    public function __construct()
    {
        $aVars = get_object_vars($this);
        unset($aVars['aMethods']);
        unset($aVars['aAlternates']);
        $aVars = array_keys($aVars);

        foreach ($aVars as $sVar) {
            $sNormalised                          = substr($sVar, 1);
            $this->aMethods['set' . $sNormalised] = $sVar;
            $this->aMethods['get' . $sNormalised] = $sVar;
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Mimics setters and getters for class properties
     *
     * @param string $sMethod    The method being called
     * @param array  $aArguments Any passed arguments
     *
     * @return $this
     * @throws NailsException
     */
    public function __call($sMethod, $aArguments)
    {
        if (array_key_exists($sMethod, $this->aMethods)) {
            if (substr($sMethod, 0, 3) === 'set') {
                $this->{$this->aMethods[$sMethod]} = reset($aArguments);
                return $this;
            } else {
                return $this->{$this->aMethods[$sMethod]};
            }
        } else {
            throw new NailsException('Call to undefined method ' . get_called_class() . '::' . $sMethod . '()');
        }
    }

    // --------------------------------------------------------------------------

    /**
     * Adds an alternate version of the link
     *
     * @param $sUrl
     * @param $sLang
     *
     * @return $this
     */
    public function setAlternate($sUrl, $sLang)
    {
        $this->aAlternates[] = Factory::factory('Url', 'nails/module-sitemap')
                                      ->setUrl($sUrl)
                                      ->setLang($sLang);
        return $this;
    }

    // --------------------------------------------------------------------------

    /**
     * Returns any alternative URLs
     * @return array
     */
    public function getAlternates()
    {
        return $this->aAlternates;
    }
}
