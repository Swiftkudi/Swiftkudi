<?php

return [
    'subdomain' => env('MARKETPLACE_SUBDOMAIN', 'campus'),
    'domain' => env('MARKETPLACE_DOMAIN', env('APP_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST))),
    'full_domain' => env('MARKETPLACE_FULL_DOMAIN', null),
    'use_subdomain' => env('MARKETPLACE_USE_SUBDOMAIN', true),
];
