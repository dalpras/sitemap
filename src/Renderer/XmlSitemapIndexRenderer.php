<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Renderer;

use DOMDocument;
use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\RendererInterface;
use DalPraS\Sitemap\Contract\UrlResolverInterface;
use DalPraS\Sitemap\Exception\RenderException;
use DalPraS\Sitemap\SitemapIndex;

final class XmlSitemapIndexRenderer implements RendererInterface
{
    private const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';

    public function __construct(
        private SitemapConfig $config,
        private UrlResolverInterface $urlResolver,
    ) {}

    public function render(object $document): string
    {
        if (!$document instanceof SitemapIndex) {
            throw new RenderException('XmlSitemapIndexRenderer can only render SitemapIndex instances.');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->config->formatOutput;

        $root = $dom->createElementNS(self::XMLNS, 'sitemapindex');
        $dom->appendChild($root);

        foreach ($document->getFiles() as $file) {
            $node = $dom->createElementNS(self::XMLNS, 'sitemap');
            $node->appendChild(
                $dom->createElementNS(self::XMLNS, 'loc', $this->urlResolver->resolve($file->getFilename()))
            );

            if ($file->getLastmod() !== null) {
                $node->appendChild(
                    $dom->createElementNS(self::XMLNS, 'lastmod', $file->getLastmod()->format('Y-m-d'))
                );
            }

            $root->appendChild($node);
        }

        $xml = $dom->saveXML();
        if ($xml === false) {
            throw new RenderException('Unable to render sitemap index.');
        }

        return $xml;
    }
}
