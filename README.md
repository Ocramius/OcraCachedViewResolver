# OcraCachedViewResolver

OcraCachedViewResolver is performance-oriented Laminas MVC 2 Module that increases performance
in your application by caching the process of resolving template names to template paths.

In [`laminas/laminas-mvc-skeleton`](https://github.com/laminas/laminas-mvc-skeleton), the process of resolving template
paths causes a lot of stat calls (disk access). This module adds a cache layer to avoid that.

| Releases | Downloads | Dependencies |
| -------- | ------- | ------------- |
| [![Latest Stable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/stable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) [![Latest Unstable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/unstable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) | [![Total Downloads](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/downloads.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) | [![Dependency Status](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver/badge.png)](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver) |

## Installation

The recommended way to install `ocramius/ocra-cached-view-resolver` is through
[composer](http://getcomposer.org/):

```sh
php composer.phar require ocramius/ocra-cached-view-resolver
```

You can then enable the module in your `config/application.config.php` by adding
`'OcraCachedViewResolver'` to the `'modules'` section.

## Configuration

Default configurations are provided in
[config/ocra-cached-view-resolver.local.php.dist](config/ocra-cached-view-resolver.local.php.dist).
You can copy it to your application's `config/autoload` directory and remove the `.dist` extension
from the file name.

Note that without this file (or similar configuration), `OcraCachedViewResolver` will use a so-called
"blackhole" cache that doesn't actually cache anything. The provided `.dist` config file assumes that
you have the `APC` extension installed: if that is not the case, please tweak this file.
