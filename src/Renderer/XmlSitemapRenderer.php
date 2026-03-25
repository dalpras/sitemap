<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Renderer;

use DOMDocument;
use DOMElement;
use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\RendererInterface;
use DalPraS\Sitemap\Contract\UrlResolverInterface;
use DalPraS\Sitemap\Exception\RenderException;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapEntry;

final class XmlSitemapRenderer implements RendererInterface
{
    private const XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    private const XMLNS_IMAGE = 'http://www.google.com/schemas/sitemap-image/1.1';
    private const XMLNS_XHTML = 'http://www.w3.org/1999/xhtml';

    public function __construct(
        private SitemapConfig $config,
        private UrlResolverInterface $urlResolver,
    ) {}

    public function render(object $document): string
    {
        if (!$document instanceof Sitemap) {
            throw new RenderException('XmlSitemapRenderer can only render Sitemap instances.');
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = $this->config->formatOutput;

        $urlSet = $dom->createElementNS(self::XMLNS, 'urlset');
        $urlSet->setAttribute('xmlns:image', self::XMLNS_IMAGE);
        $urlSet->setAttribute('xmlns:xhtml', self::XMLNS_XHTML);
        $dom->appendChild($urlSet);

        foreach ($document->getEntries() as $entry) {
            $urlSet->appendChild($this->renderEntry($dom, $entry));
        }

        $xml = $dom->saveXML();
        if ($xml === false) {
            throw new RenderException(sprintf('Unable to render sitemap "%s".', $document->getName()));
        }

        return $xml;
    }

    private function renderEntry(DOMDocument $dom, SitemapEntry $entry): DOMElement
    {
        $url = $dom->createElementNS(self::XMLNS, 'url');

        $url->appendChild(
            $dom->createElementNS(self::XMLNS, 'loc', $this->urlResolver->resolve($entry->getLoc()))
        );

        if ($entry->getLastmod() !== null) {
            $url->appendChild(
                $dom->createElementNS(self::XMLNS, 'lastmod', $entry->getLastmod()->format('Y-m-d'))
            );
        }

        if ($entry->getChangefreq() !== null) {
            $url->appendChild(
                $dom->createElementNS(self::XMLNS, 'changefreq', $entry->getChangefreq())
            );
        }

        if ($entry->getPriority() !== null) {
            $url->appendChild(
                $dom->createElementNS(self::XMLNS, 'priority', number_format($entry->getPriority(), 1, '.', ''))
            );
        }

        foreach ($entry->getAlternates() as $alternate) {
            $link = $dom->createElementNS(self::XMLNS_XHTML, 'xhtml:link');
            $link->setAttribute('rel', 'alternate');
            $link->setAttribute('hreflang', $alternate->getHrefLang());
            $link->setAttribute('href', $this->urlResolver->resolve($alternate->getHref()));
            $url->appendChild($link);
        }

        foreach ($entry->getImages() as $image) {
            $imageNode = $dom->createElement('image:image');
            $imageNode->appendChild(
                $dom->createElement('image:loc', $this->urlResolver->resolve($image->getLoc()))
            );

            if ($image->getCaption() !== null) {
                $imageNode->appendChild($dom->createElement('image:caption', $image->getCaption()));
            }

            if ($image->getTitle() !== null) {
                $imageNode->appendChild($dom->createElement('image:title', $image->getTitle()));
            }

            $url->appendChild($imageNode);
        }

        return $url;
    }
}
