<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Service;

use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\OutputWriterInterface;
use DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer;
use DalPraS\Sitemap\Renderer\XmlSitemapRenderer;
use DalPraS\Sitemap\Support\BaseUrlResolver;
use DalPraS\Sitemap\Support\FilesystemWriter;
use DalPraS\Sitemap\Support\FilesystemWriterFactory;
use DalPraS\Sitemap\Support\SitemapSplitter;
use DalPraS\Sitemap\Support\SitemapValidator;
use LogicException;

final class SitemapRuntimeContextFactory
{
    public function __construct(
        private readonly SitemapConfig $config,
        private readonly OutputWriterInterface $writer,
        private readonly ?FilesystemWriterFactory $filesystemWriterFactory = null,
    ) {}

    public function create(
        ?string $entryBaseUrl = null,
        ?string $sitemapBaseUrl = null,
        ?string $folder = null,
    ): SitemapRuntimeContext {
        $config = $this->resolveConfig($entryBaseUrl, $sitemapBaseUrl);

        $entryUrlResolver = new BaseUrlResolver(
            baseUrl: $config->entryBaseUrl,
            allowAbsoluteUrls: $config->allowAbsoluteUrls,
        );

        $sitemapUrlResolver = new BaseUrlResolver(
            baseUrl: $config->sitemapBaseUrl,
            allowAbsoluteUrls: $config->allowAbsoluteUrls,
        );

        $sitemapRenderer = new XmlSitemapRenderer(
            config: $config,
            urlResolver: $entryUrlResolver,
        );

        $indexRenderer = new XmlSitemapIndexRenderer(
            config: $config,
            urlResolver: $sitemapUrlResolver,
        );

        $splitter = new SitemapSplitter($config);
        $validator = new SitemapValidator($config->strictValidation);

        $writer = $this->resolveWriter($folder);

        $generator = new SitemapGenerator(
            config: $config,
            validator: $validator,
            splitter: $splitter,
            sitemapRenderer: $sitemapRenderer,
            indexRenderer: $indexRenderer,
            writer: $writer,
        );

        return new SitemapRuntimeContext(
            generator: $generator,
            indexRenderer: $indexRenderer,
            writer: $writer,
        );
    }

    private function resolveConfig(
        ?string $entryBaseUrl,
        ?string $sitemapBaseUrl,
    ): SitemapConfig {
        if ($entryBaseUrl === null || $sitemapBaseUrl === null) {
            throw new LogicException('Both entryBaseUrl and sitemapBaseUrl must be configured.');
        }

        $config = $this->config;

        if (rtrim($entryBaseUrl, '/') !== $config->entryBaseUrl) {
            $config = $config->withEntryBaseUrl($entryBaseUrl);
        }

        if (rtrim($sitemapBaseUrl, '/') !== $config->sitemapBaseUrl) {
            $config = $config->withSitemapBaseUrl($sitemapBaseUrl);
        }

        return $config;
    }

    private function resolveWriter(?string $folder): OutputWriterInterface
    {
        if ($folder === null) {
            return $this->writer;
        }

        if ($this->filesystemWriterFactory !== null) {
            return $this->filesystemWriterFactory->create($folder);
        }

        if ($this->writer instanceof FilesystemWriter) {
            return $this->writer->withFolder($folder);
        }

        throw new LogicException('Runtime folder override is only supported with FilesystemWriter.');
    }
}
