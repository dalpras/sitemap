<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Legacy;

use DateTimeImmutable;
use RecursiveIterator;
use RecursiveIteratorIterator;
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

final class SitemapBuilder
{
    /** @var array<string, Sitemap> */
    private array $sitemaps = [];

    private SitemapGenerator $generator;

    public function __construct(
        private string $folder,
        private string $entryBaseUrl = 'http://localhost',
        private string $sitemapBaseUrl = 'http://localhost/sitemaps',
    ) {
        $config = new SitemapConfig(
            entryBaseUrl: $entryBaseUrl,
            sitemapBaseUrl: $sitemapBaseUrl,
        );

        $entryResolver = new BaseUrlResolver($config->entryBaseUrl, $config->allowAbsoluteUrls);
        $sitemapResolver = new BaseUrlResolver($config->sitemapBaseUrl, $config->allowAbsoluteUrls);

        $this->generator = new SitemapGenerator(
            config: $config,
            validator: new SitemapValidator($config->strictValidation),
            splitter: new SitemapSplitter($config),
            sitemapRenderer: new XmlSitemapRenderer($config, $entryResolver),
            indexRenderer: new XmlSitemapIndexRenderer($config, $sitemapResolver),
            writer: new FilesystemWriter($folder),
        );
    }

    public function addEntry(
        string $mapname,
        string $loc,
        ?string $lastmod = null,
        array $images = [],
        array $alternates = []
    ): static {
        $sitemap = $this->getOrCreate($mapname);

        $date = null;
        if ($lastmod !== null) {
            $date = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $lastmod) ?: null;
        }

        $entry = new SitemapEntry($loc, $date);

        foreach ($alternates as $alternate) {
            $lang = $alternate['lang'] ?? null;
            $country = $alternate['country'] ?? ($alternate['market'] ?? null);
            $href = $alternate['href'] ?? null;

            if (is_string($lang) && $lang !== '' && is_string($href) && $href !== '') {
                $hreflang = (is_string($country) && $country !== '')
                    ? $lang . '-' . $country
                    : $lang;

                $entry->addAlternate(new AlternateLink($hreflang, $href));
            }
        }

        foreach ($images as $image) {
            if (is_string($image) && $image !== '') {
                $entry->addImage(new ImageReference($image));
            }
        }

        $sitemap->addEntry($entry);

        return $this;
    }

    public function addNavigation(string $mapname, RecursiveIterator $container, array $alternates): static
    {
        $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $page) {
            $page->setRoute('route-lang-market');

            $entry = null;

            foreach ($alternates as $index => $alternate) {
                $lang = $alternate['lang'] ?? null;
                $country = $alternate['country'] ?? ($alternate['market'] ?? null);

                if (!is_string($lang) || !is_string($country) || $lang === '' || $country === '') {
                    continue;
                }

                $page->setParam('lang', $lang)
                    ->setParam('country', $country);

                $href = $page->getHref();
                $hreflang = sprintf('%s-%s', $lang, $country);

                if ($index === 0) {
                    $entry = new SitemapEntry($href);
                }

                if ($entry !== null) {
                    $entry->addAlternate(new AlternateLink($hreflang, $href));
                }
            }

            if ($entry !== null) {
                $this->getOrCreate($mapname)->addEntry($entry);
            }
        }

        return $this;
    }

    public function save(): static
    {
        foreach ($this->sitemaps as $sitemap) {
            $this->generator->generate($sitemap);
        }

        return $this;
    }

    public function clean(): static
    {
        $this->sitemaps = [];
        return $this;
    }

    private function getOrCreate(string $name): Sitemap
    {
        return $this->sitemaps[$name] ??= new Sitemap($name);
    }
}
