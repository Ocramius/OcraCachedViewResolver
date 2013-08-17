# OcraCachedViewResolver

OcraCachedViewResolver is performance-oriented Zend Framework 2 Module that increases performance
in your application by caching the process of resolving template names to paths.

In ZF2, the process of resolving template paths causes a lot of stat calls. This module adds
a cache layer to avoid that.

| Tests | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- | --------- | ------------ |
|[![Build Status](https://travis-ci.org/Ocramius/OcraCachedViewResolver.png?branch=master)](https://travis-ci.org/Ocramius/OcraCachedViewResolver) [![Coverage Status](https://coveralls.io/repos/Ocramius/OcraCachedViewResolver/badge.png?branch=master)](https://coveralls.io/r/Ocramius/OcraCachedViewResolver)|[![Latest Stable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/stable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) [![Latest Unstable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/unstable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)|[![Total Downloads](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/downloads.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)|[![Dependency Status](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver/badge.png)](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver)|

## Installation

The recommended way to install `ocramius/ocra-cached-view-resolver` is through
[composer](http://getcomposer.org/):

```sh
php composer.phar require ocramius/ocra-cached-view-resolver:*
```

You can then enable the module in your `config/application.config.php`.

## Configuration

The only configurable setting for this module is how the cache adapter has to be generated.
By default, a `Zend\Cache\Storage\Adapter\Apc` is used. You can change this behavior by
defining following config in your application:

```php
return array(
    'ocra_cached_view_resolver' => array(
        'cache' => array(
            // configuration to be passed to `Zend\Cache\StorageFactory#factory()` here
        ),

        // following is the key used to store the template map in the cache adapter
        'cached_template_map_key' => 'cached_template_map',
    ),
);
```

### Prevent cache collisions

APC is shared memory. Depending on your server configuration this might lead to conflicts between projects. To prevent (ugly) collisions in the shared APC the following trick (or something similar) could be employed.

```php
    'ocra_cached_view_resolver' => array(
        'cached_template_map_key' => realpath(__DIR__)
    ),
```

## Testing

After having installed via composer:

```sh
cd path/to/ocra-cached-view-resolver
phpunit
```


