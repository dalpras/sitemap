<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Service;

use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\OutputWriterInterface;
use DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer;
use DalPraS\Sitemap\Renderer\XmlSitemapRenderer;
use DalPraS\Sitemap\Support\FilesystemWriter;
use DalPraS\Sitemap\Support\SitemapSplitter;
use DalPraS\Sitemap\Support\SitemapValidator;

final class SitemapGeneratorFactory
{
    public function __construct(
        private readonly SitemapConfig $config,
        private readonly SitemapValidator $validator,
        private readonly SitemapSplitter $splitter,
        private readonly XmlSitemapRenderer $sitemapRenderer,
        private readonly XmlSitemapIndexRenderer $indexRenderer,
        private readonly OutputWriterInterface $writer,
    ) {}

    public function create(?string $baseUrl = null, ?string $folder = null): SitemapGenerator
    {
        $config = $baseUrl !== null
            ? $this->config->withBaseUrl($baseUrl)
            : $this->config;

        $writer = $this->writer;

        if ($folder !== null) {
            if (!$writer instanceof FilesystemWriter) {
                throw new \LogicException('Runtime folder override is only supported with FilesystemWriter.');
            }

            $writer = $writer->withFolder($folder);
        }

        return new SitemapGenerator(
            config: $config,
            validator: $this->validator,
            splitter: $this->splitter,
            sitemapRenderer: $this->sitemapRenderer,
            indexRenderer: $this->indexRenderer,
            writer: $writer,
        );
    }
}