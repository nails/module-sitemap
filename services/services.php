<?php

return [
    'services'  => [
        'SiteMap' => function () {
            if (class_exists('\App\SiteMap\Service\SiteMap')) {
                return new \App\SiteMap\Service\SiteMap();
            } else {
                return new \Nails\SiteMap\Service\SiteMap();
            }
        },
    ],
    'factories' => [
        'Url' => function () {
            if (class_exists('\App\SiteMap\Factory\Url')) {
                return new \App\SiteMap\Factory\Url();
            } else {
                return new \Nails\SiteMap\Factory\Url();
            }
        },
    ],
];
