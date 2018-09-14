# Docs for `nails/module-sitemap`


This module brings file sitemap capabilities to Nails.



## Generating the sitemap

This module provides the `SiteMap` service; to generate the site map, call the service's `write()` method.

```php
$oSiteMap = Factory::service('SiteMap', 'nails/module-sitemap');
$oSiteMap->write();
```

The sitemap will be written to `./sitemap.xml`.



## Generators

"Generators" provide the SiteMap service with URLs to include; any installed module (or the application) can provide a generator. The generator must live within the app or module's namespace at `*\SiteMap\Generator()` and implement the `Nails\SiteMap\Interfaaces\Generator` interface.

The interface enforces a public method, `execute()`, which takes no arguments and returns an array of `Nails\SiteMap\Factory\Url()` objects.



## Factories

A new instance of `Nails\SiteMap\Factory\Url()` can be fetched via the Nails Factory:

```php
$oUrl = Factory::factory('Url', 'nails/module-sitemap');
```

The following properties can be set:

- URL
- Lang (ISO 639-1 language code)
- ChangeFrequency (defaults to `weekly`)
- Priority (defaults to `0.5`)
- Modified

In addition, alternate language versions fo the URL can also be set, using the `addAlternate($sUrl, $sLang)` method.

Example generator:

```php
<?php

namespace App\SiteMap;

class Generator
{
    public function execute()
    {
		return [
			Factory::factory('Url', 'nails/module-sitemap')
				->setUrl(siteUrl('some-page'))
				->setChangeFrequency('daily')
				->setPriority(0.75)
				->setAlternate(siteUrl('jp/some-page'), 'ja')
				->setAlternate(siteUrl('fr/some-page'), 'fr')
		]
    }
}


```



## Console

The following console command is made available by this module:

```
nails sitemap:generate
```

This will, as the name implies, generate the sitemap; you may wish to call this command regularly using cron to keep the sitemap up to date.
