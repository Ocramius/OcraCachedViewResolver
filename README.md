# OcraCachedViewResolver

OcraCachedViewResolver is performance-oriented Zend Framework 2 Module that increases performance
in your application by caching the process of resolving template names to template paths.

In ZF3, the process of resolving template paths causes a lot of stat calls. This module adds
a cache layer to avoid that.

| Tests | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- |
|[![Build Status](https://travis-ci.org/Ocramius/OcraCachedViewResolver.png?branch=master)](https://travis-ci.org/Ocramius/OcraCachedViewResolver) [![Code Coverage](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/badges/coverage.png?s=2e8b79821b59bfccea8e4fcdec087df12d13be96)](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/badges/quality-score.png?s=30f146bf14c64a11d4bc304c4e7786e4016786c0)](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/) | [![Latest Stable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/stable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) [![Latest Unstable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/unstable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) | [![Total Downloads](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/downloads.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) | [![Dependency Status](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver/badge.png)](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver) |

## Installation

The recommended way to install `ocramius/ocra-cached-view-resolver` is through
[composer](http://getcomposer.org/):

```sh
php composer.phar require ocramius/ocra-cached-view-resolver:3.0.*
```

If you use legacy/outdated PHP versions, such as `5.5.x` and `5.6.x`, you can use any
[`3.x`](https://github.com/Ocramius/OcraCachedViewResolver/tree/3.0.0) version
of `ocramius/ocra-cached-view-resolver`.

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
