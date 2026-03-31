# dalpras/sitemap

A small PHP library for generating XML sitemaps and sitemap indexes, with support for validation, splitting large sitemaps, and writing output to the filesystem.

## Features

- Generate XML sitemaps from `Sitemap` and `SitemapEntry` objects
- Automatically split large sitemaps into multiple files
- Generate sitemap index files when a sitemap is split
- Optional gzip output
- Validation support before writing files
- Filesystem writer implementation out of the box
- Runtime factories for:
  - `baseUrl`
  - output directory

## Installation

Install with Composer:

```bash
composer require dalpras/sitemap
```

## Core concepts

### `Sitemap`

A sitemap container identified by a name.

### `SitemapEntry`

Represents a single URL entry in a sitemap.

### `SitemapGenerator`

Validates entries, splits large sitemaps when necessary, renders XML, and writes output.

### `SitemapConfig`

Controls generator behavior, including:

- `baseUrl`
- `formatOutput`
- `allowAbsoluteUrls`
- `gzip`
- `maxEntriesPerFile`
- `maxUncompressedBytesPerFile`
- `strictValidation`

### `FilesystemWriter`

Writes generated sitemap files to a target folder.

## Basic example

```php
<?php declare(strict_types=1);

use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;

$sitemap = new Sitemap('pages');
$sitemap->addEntry(new SitemapEntry('/en/about'));
$sitemap->addEntry(new SitemapEntry('/en/contact'));

$generator->generate($sitemap);
```

## Runtime factories

The library supports creating generators at runtime with a different base URL and output folder.

This is useful when the same application needs to generate multiple sitemap sets in a single execution, such as one sitemap per domain, storefront, or tenant.

Instead of mutating shared services, the library uses immutable helpers and a factory.

### Why factories?

In most container-based applications, `SitemapConfig` and `FilesystemWriter` are shared services.

Changing them directly during command execution would make them stateful and unsafe. Factories avoid that by creating a fresh `SitemapGenerator` with runtime-specific values.

## Immutable runtime helpers

### `SitemapConfig::withBaseUrl()`

Returns a new config instance with a different `baseUrl`, preserving the rest of the configuration.

```php
$config = $config->withBaseUrl('https://www.example.com');
```

### `FilesystemWriter::withFolder()`

Returns a new writer instance targeting a different folder.

```php
$writer = $writer->withFolder('/var/www/project/public/sitemaps');
```

## `SitemapGeneratorFactory`

Use `SitemapGeneratorFactory` to create a fresh generator for each runtime context.

```php
$generator = $factory->create(
    baseUrl: 'https://www.example.com',
    folder: '/var/www/project/public/sitemaps'
);
```

This allows you to reuse the default library services while changing:

- the runtime base URL
- the output directory

## Example: generate a sitemap with runtime configuration

```php
<?php declare(strict_types=1);

use DalPraS\Sitemap\Service\SitemapGeneratorFactory;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;

final class BuildSitemap
{
    public function __construct(
        private SitemapGeneratorFactory $sitemapGeneratorFactory,
    ) {}

    public function run(): void
    {
        $sitemap = new Sitemap('pages');
        $sitemap->addEntry(new SitemapEntry('/en/about'));
        $sitemap->addEntry(new SitemapEntry('/en/contact'));

        $generator = $this->sitemapGeneratorFactory->create(
            baseUrl: 'https://www.example.com',
            folder: __DIR__ . '/public/sitemaps'
        );

        $generator->generate($sitemap);
    }
}
```

## Example: generate multiple sitemap sets in one command

Factories are especially useful when generating multiple sitemap groups in a single process.

```php
<?php declare(strict_types=1);

use DalPraS\Sitemap\Service\SitemapGeneratorFactory;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;

final class BuildAllSitemaps
{
    public function __construct(
        private SitemapGeneratorFactory $sitemapGeneratorFactory,
    ) {}

    public function run(): void
    {
        $sites = [
            'site-a' => 'https://www.site-a.example',
            'site-b' => 'https://www.site-b.example',
        ];

        foreach ($sites as $key => $baseUrl) {
            $sitemap = new Sitemap('catalog-' . $key);
            $sitemap->addEntry(new SitemapEntry('/en/products/example'));

            $generator = $this->sitemapGeneratorFactory->create(
                baseUrl: $baseUrl,
                folder: __DIR__ . '/public/sitemaps/' . $key
            );

            $generator->generate($sitemap);
        }
    }
}
```

This keeps services immutable while allowing each sitemap run to target a different domain and folder.

## Factory API

### `SitemapGeneratorFactory::create()`

```php
public function create(?string $baseUrl = null, ?string $folder = null): SitemapGenerator
```

Parameters:

- `baseUrl`: optional runtime override for `SitemapConfig::baseUrl`
- `folder`: optional runtime override for `FilesystemWriter`

## Symfony service registration example

```yaml
services:
  DalPraS\Sitemap\Service\SitemapGeneratorFactory:
    arguments:
      $config: '@DalPraS\Sitemap\Config\SitemapConfig'
      $validator: '@DalPraS\Sitemap\Support\SitemapValidator'
      $splitter: '@DalPraS\Sitemap\Support\SitemapSplitter'
      $sitemapRenderer: '@DalPraS\Sitemap\Renderer\XmlSitemapRenderer'
      $indexRenderer: '@DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer'
      $writer: '@DalPraS\Sitemap\Contract\OutputWriterInterface'
```

## Notes

### Base URL behavior

The factory allows you to override `baseUrl` at runtime by creating a new `SitemapConfig` instance.

```php
$generator = $factory->create(baseUrl: 'https://www.example.com');
```

Whether this changes the final XML depends on which components use `SitemapConfig::baseUrl`.

If your application already builds fully qualified URLs before creating `SitemapEntry` objects, overriding `baseUrl` may not change the generated XML.

### Output folder override

The runtime `folder` override is supported when the configured writer is `FilesystemWriter`.

```php
$generator = $factory->create(folder: '/tmp/sitemaps');
```

If another writer implementation is used, runtime folder override may not be available.

## Recommended usage

Use the factory whenever:

- you generate sitemaps for multiple domains in one process
- you need different output folders per run
- you want to keep services immutable and container-safe

Avoid mutating shared config or writer services during command execution.

## What changed

### Added

- `SitemapConfig::withBaseUrl()` for immutable runtime base URL overrides
- `FilesystemWriter::withFolder()` for immutable runtime folder overrides
- `SitemapGeneratorFactory` for creating generators with per-run configuration

### Typical use case

These additions make it easier to generate sitemaps for multiple domains, tenants, storefronts, or environments within the same command without mutating shared services.

## License

MIT
