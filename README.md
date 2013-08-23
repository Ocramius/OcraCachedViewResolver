# OcraCachedViewResolver

OcraCachedViewResolver is performance-oriented Zend Framework 2 Module that increases performance
in your application by caching the process of resolving template names to template paths.

In ZF2, the process of resolving template paths causes a lot of stat calls. This module adds
a cache layer to avoid that.

| Tests | Releases | Downloads | Dependencies |
| ----- | -------- | ------- | ------------- | --------- | ------------ |
|[![Build Status](https://travis-ci.org/Ocramius/OcraCachedViewResolver.png?branch=master)](https://travis-ci.org/Ocramius/OcraCachedViewResolver) [![Coverage Status](https://coveralls.io/repos/Ocramius/OcraCachedViewResolver/badge.png?branch=master)](https://coveralls.io/r/Ocramius/OcraCachedViewResolver) [![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/badges/quality-score.png?s=30f146bf14c64a11d4bc304c4e7786e4016786c0)](https://scrutinizer-ci.com/g/Ocramius/OcraCachedViewResolver/)|[![Latest Stable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/stable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver) [![Latest Unstable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/unstable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)|[![Total Downloads](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/downloads.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)|[![Dependency Status](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver/badge.png)](https://www.versioneye.com/package/php--ocramius--ocra-cached-view-resolver)|

## Installation

The recommended way to install `ocramius/ocra-cached-view-resolver` is through
[composer](http://getcomposer.org/):

```sh
php composer.phar require ocramius/ocra-cached-view-resolver:*
```

You can then enable the module in your `config/application.config.php` by adding
`'OcraCachedViewResolver'` to the `'modules'` section.

## Configuration

Default configurations are provided in
[config/ocra-cached-view-resolver.local.php.dist](config/ocra-cached-view-resolver.local.php.dist).
You can copy it to your application's `config/autoload` directory and remove the `.dist` extension
from the file name.
