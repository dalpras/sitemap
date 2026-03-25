<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Sitemap;

final class SitemapSplitter
{
    public function __construct(
        private SitemapConfig $config,
    ) {}

    /**
     * @return list<Sitemap>
     */
    public function split(Sitemap $sitemap): array
    {
        $entries = $sitemap->getEntries();
        $max = $this->config->maxEntriesPerFile;

        if (count($entries) <= $max) {
            return [$sitemap];
        }

        $chunks = array_chunk($entries, $max);
        $result = [];

        foreach ($chunks as $index => $chunk) {
            $chunked = new Sitemap(sprintf('%s-%d', $sitemap->getName(), $index + 1));
            foreach ($chunk as $entry) {
                $chunked->addEntry($entry);
            }
            $result[] = $chunked;
        }

        return $result;
    }
}
