# OcraCachedViewResolver

OcraCachedViewResolver is performance-oriented Laminas MVC 2 Module that increases performance
in your application by caching the process of resolving template names to template paths.

In [`laminas/laminas-mvc-skeleton`](https://github.com/laminas/laminas-mvc-skeleton), the process of resolving template
paths causes a lot of stat calls (disk access). This module adds a cache layer to avoid that.

[![Latest Stable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/stable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)
[![Latest Unstable Version](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/v/unstable.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)
[![Total Downloads](https://poser.pugx.org/ocramius/ocra-cached-view-resolver/downloads.png)](https://packagist.org/packages/ocramius/ocra-cached-view-resolver)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FOcramius%2FOcraCachedViewResolver%2F6.0.x)](https://dashboard.stryker-mutator.io/reports/github.com/Ocramius/OcraCachedViewResolver/6.0.x)

## Installation

The recommended way to install `ocramius/ocra-cached-view-resolver` is through
[composer](http://getcomposer.org/):

```sh
php composer.phar require \
  ocramius/ocra-cached-view-resolver \
  laminas/laminas-cache-storage-adapter-filesystem
```

You can pick any [`laminas/laminas-cache-storage-implementation`](https://packagist.org/providers/laminas/laminas-cache-storage-implementation)
you want, as long as you provide one, and later configure it.

You can then enable the module in your `config/application.config.php` by adding
`'OcraCachedViewResolver'` to the `'modules'` section.

## Configuration

Default configurations are provided in
[`config/ocra-cached-view-resolver.local.php.dist`](config/ocra-cached-view-resolver.local.php.dist).
You can copy it to your application's `config/autoload` directory and remove the `.dist` extension
from the file name, then adjust its contents to your needs.

Note that you will need to provide your own `laminas/laminas-cache` adapter (pick one of your choice),
and configure it as service to be referenced in your copy of `config/ocra-cached-view-resolver.local.php.dist`.
