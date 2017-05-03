<?php

return [
    'models' => [
        'SiteMap' => function () {
            if (class_exists('\App\SiteMap\Model\SiteMap')) {
                return new \App\SiteMap\Model\SiteMap();
            } else {
                return new \Nails\SiteMap\Model\SiteMap();
            }
        },
    ],
];
