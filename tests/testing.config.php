<?php

declare(strict_types=1);

use Laminas\Cache\Storage\Adapter\Memory;
use OcraCachedViewResolver\Module;

return [
    Module::CONFIG => [
        Module::CONFIG_CACHE_KEY     => 'testing_cache_key',
        Module::CONFIG_CACHE_SERVICE => Memory::class,
    ],
    'service_manager'=> [
        'invokables' => [
            Memory::class => Memory::class,
        ]
    ]
];
