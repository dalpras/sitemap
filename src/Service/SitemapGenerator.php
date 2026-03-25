<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Service;

use DateTimeImmutable;
use RuntimeException;
use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\OutputWriterInterface;
use DalPraS\Sitemap\Renderer\XmlSitemapIndexRenderer;
use DalPraS\Sitemap\Renderer\XmlSitemapRenderer;
use DalPraS\Sitemap\Sitemap;
use DalPraS\Sitemap\SitemapFile;
use DalPraS\Sitemap\SitemapIndex;
use DalPraS\Sitemap\Support\SitemapSplitter;
use DalPraS\Sitemap\Support\SitemapValidator;

final class SitemapGenerator
{
    public function __construct(
        private SitemapConfig $config,
        private SitemapValidator $validator,
        private SitemapSplitter $splitter,
        private XmlSitemapRenderer $sitemapRenderer,
        private XmlSitemapIndexRenderer $indexRenderer,
        private OutputWriterInterface $writer,
    ) {}

    public function generate(Sitemap $sitemap): void
    {
        foreach ($sitemap->getEntries() as $entry) {
            $this->validator->validateEntry($entry);
        }

        $chunks = $this->splitter->split($sitemap);
        $index = new SitemapIndex();
        $now = new DateTimeImmutable();

        foreach ($chunks as $chunk) {
            $filename = $chunk->getName() . '-sitemap.xml';
            $content = $this->sitemapRenderer->render($chunk);

            if ($this->config->gzip) {
                $filename .= '.gz';
                $content = gzencode($content);
                if ($content === false) {
                    throw new RuntimeException(sprintf('Unable to gzip sitemap "%s".', $chunk->getName()));
                }
            }

            $this->writer->write($filename, $content);
            $index->addFile(new SitemapFile($filename, '', $now));
        }

        if (count($chunks) > 1) {
            $indexFilename = $sitemap->getName() . '-sitemap-index.xml';
            $indexContent = $this->indexRenderer->render($index);
            $this->writer->write($indexFilename, $indexContent);
        }
    }
}
