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

    public function create(?string $baseUrl = null, ?string $folder = null): SitemapRuntimeContext
    {
        $config = $baseUrl !== null
            ? $this->config->withBaseUrl($baseUrl)
            : $this->config;

        $urlResolver = new BaseUrlResolver($config);

        $sitemapRenderer = new XmlSitemapRenderer(
            config: $config,
            urlResolver: $urlResolver,
        );

        $indexRenderer = new XmlSitemapIndexRenderer(
            config: $config,
            urlResolver: $urlResolver,
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