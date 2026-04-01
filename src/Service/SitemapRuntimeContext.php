<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Service;

use DalPraS\Sitemap\Contract\OutputWriterInterface;
use DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer;

final class SitemapRuntimeContext
{
    public function __construct(
        public readonly SitemapGenerator $generator,
        public readonly XmlSitemapIndexRenderer $indexRenderer,
        public readonly OutputWriterInterface $writer,
    ) {}
}