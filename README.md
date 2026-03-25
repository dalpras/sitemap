# DalPraS Sitemap

Libreria PHP per generare sitemap XML con supporto a:

- URL entries
- hreflang alternates
- immagini
- split automatico per numero di entry
- sitemap index
- gzip opzionale
- facade legacy compatibile con `SitemapBuilder`

## Namespace

```php
use DalPraS\Sitemap\Sitemap;
```

## Installazione

Aggiungi il package al tuo progetto oppure copia i file in una libreria interna e configura l'autoload PSR-4:

```json
{
  "autoload": {
    "psr-4": {
      "DalPraS\\Sitemap\\": "src/"
    }
  }
}
```

## Uso base

```php
use DateTimeImmutable;
use DalPraS\Sitemap\AlternateLink;
use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\ImageReference;
use DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer;
use DalPraS\Sitemap\Renderer\XmlSitemapRenderer;
use DalPraS\Sitemap\Service\SitemapGenerator;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;
use DalPraS\Sitemap\Support\BaseUrlResolver;
use DalPraS\Sitemap\Support\FilesystemWriter;
use DalPraS\Sitemap\Support\SitemapSplitter;
use DalPraS\Sitemap\Support\SitemapValidator;

$config = new SitemapConfig(
    baseUrl: 'https://www.example.com',
    gzip: false,
    maxEntriesPerFile: 50000,
    strictValidation: true,
);

$resolver = new BaseUrlResolver($config);

$generator = new SitemapGenerator(
    config: $config,
    validator: new SitemapValidator($config->strictValidation),
    splitter: new SitemapSplitter($config),
    sitemapRenderer: new XmlSitemapRenderer($config, $resolver),
    indexRenderer: new XmlSitemapIndexRenderer($config, $resolver),
    writer: new FilesystemWriter(__DIR__ . '/sitemaps'),
);

$sitemap = new Sitemap('catalog');

$entry = new SitemapEntry(
    loc: '/it/prodotti/interruttore-123',
    lastmod: new DateTimeImmutable('2026-03-17'),
    changefreq: 'weekly',
    priority: 0.8,
);

$entry
    ->addAlternate(new AlternateLink('it-IT', '/it/prodotti/interruttore-123'))
    ->addAlternate(new AlternateLink('en-GB', '/en/products/switch-123'))
    ->addImage(new ImageReference('/media/catalog/interruttore-123.jpg', 'Interruttore Vimar', 'Interruttore 123'));

$sitemap->addEntry($entry);
$generator->generate($sitemap);
```

## Facade legacy

```php
use DalPraS\Sitemap\Legacy\SitemapBuilder;

$builder = new SitemapBuilder(__DIR__ . '/sitemaps', 'https://www.example.com');
$builder
    ->addEntry('catalog', '/it/prodotti/demo', '2026-03-17 10:00:00')
    ->save();
```
