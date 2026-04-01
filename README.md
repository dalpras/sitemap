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
  - `entryBaseUrl`
  - `sitemapBaseUrl`
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

- `entryBaseUrl`
- `sitemapBaseUrl`
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

The library supports creating generators at runtime with different entry and sitemap base URLs, plus a different output folder.

This is useful when the same application needs to generate multiple sitemap sets in a single execution, such as one sitemap per domain, storefront, or tenant.

Instead of mutating shared services, the library uses immutable config helpers and a factory.

### Why factories?

In most container-based applications, `SitemapConfig` and `FilesystemWriter` are shared services.

Changing them directly during command execution would make them stateful and unsafe. Factories avoid that by creating a fresh `SitemapGenerator` with runtime-specific values.

## Immutable config helpers

### `SitemapConfig::withEntryBaseUrl()`

Returns a new config instance with a different `entryBaseUrl`, preserving the rest of the configuration.

```php
$config = $config->withEntryBaseUrl('https://www.example.com');
```

### `SitemapConfig::withSitemapBaseUrl()`

Returns a new config instance with a different `sitemapBaseUrl`, preserving the rest of the configuration.

```php
$config = $config->withSitemapBaseUrl('https://www.example.com/sitemaps');
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
    entryBaseUrl: 'https://www.example.com',
    sitemapBaseUrl: 'https://www.example.com/sitemaps',
    folder: '/var/www/project/public/sitemaps'
);
```

This allows you to reuse the default library services while changing:

- the runtime entry base URL
- the runtime sitemap base URL
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
            entryBaseUrl: 'https://www.example.com',
            sitemapBaseUrl: 'https://www.example.com/sitemaps',
            folder: __DIR__ . '/public/sitemaps'
        );

        $generator->generate($sitemap);
    }
}
```

## Example: absolute entry URLs with separate sitemap file base URL

This is the recommended setup when your application already knows the canonical entry URLs and stores sitemap files under a public `/sitemaps` path.

```php
<?php declare(strict_types=1);

use DalPraS\Sitemap\Service\SitemapGeneratorFactory;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;

$generator = $factory->create(
    entryBaseUrl: 'https://www.example.com',
    sitemapBaseUrl: 'https://www.example.com/sitemaps',
    folder: __DIR__ . '/public/sitemaps',
);

$sitemap = new Sitemap('catalog');
$sitemap->addEntry(new SitemapEntry('https://www.example.com/en/products/example'));

$generator->generate($sitemap);
```

In this setup:

- sitemap entries keep their canonical absolute URLs
- sitemap index files are linked under `https://www.example.com/sitemaps`

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

        foreach ($sites as $key => $siteBaseUrl) {
            $sitemap = new Sitemap('catalog-' . $key);
            $sitemap->addEntry(new SitemapEntry('/en/products/example'));

            $generator = $this->sitemapGeneratorFactory->create(
                entryBaseUrl: $siteBaseUrl,
                sitemapBaseUrl: rtrim($siteBaseUrl, '/') . '/sitemaps',
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
public function create(
    ?string $entryBaseUrl = null,
    ?string $sitemapBaseUrl = null,
    ?string $folder = null,
): SitemapGenerator
```

Parameters:

- `entryBaseUrl`: runtime override for `SitemapConfig::entryBaseUrl`
- `sitemapBaseUrl`: runtime override for `SitemapConfig::sitemapBaseUrl`
- `folder`: optional runtime override for `FilesystemWriter`

## Recommended usage

Use the explicit API:

```php
$generator = $factory->create(
    entryBaseUrl: 'https://www.example.com',
    sitemapBaseUrl: 'https://www.example.com/sitemaps',
    folder: '/var/www/project/public/sitemaps',
);
```

Use `baseUrl` only for backward compatibility when both bases are intentionally the same:

```php
$generator = $factory->create(
    baseUrl: 'https://www.example.com',
    folder: '/var/www/project/public/sitemaps',
);
```

## Notes

### URL resolution behavior

- `entryBaseUrl` is used to resolve non-absolute sitemap entry URLs, image URLs, and alternate links
- `sitemapBaseUrl` is used to resolve sitemap file URLs inside sitemap indexes
- if `allowAbsoluteUrls` is enabled, already absolute URLs are left unchanged

This separation avoids mistakes when sitemap files are publicly served from a subpath such as `/sitemaps`.

### Output folder override

The runtime `folder` override is supported when the configured writer is `FilesystemWriter`.

```php
$generator = $factory->create(folder: '/tmp/sitemaps');
```

If another writer implementation is used, runtime folder override may not be available.

## What changed

### Added

- `SitemapConfig::withEntryBaseUrl()` for immutable runtime entry base URL overrides
- `SitemapConfig::withSitemapBaseUrl()` for immutable runtime sitemap base URL overrides
- separate runtime URL resolvers for sitemap entries and sitemap index files
- explicit `entryBaseUrl` and `sitemapBaseUrl` support in runtime factories

### Deprecated

- factory-level `baseUrl` as the primary public API; keep using it only as a legacy shorthand when both bases are the same
