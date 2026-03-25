<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DalPraS\Sitemap\Exception\InvalidSitemapEntryException;
use DalPraS\Sitemap\SitemapEntry;

final class SitemapValidator
{
    public function __construct(
        private bool $strict = true,
    ) {}

    public function validateEntry(SitemapEntry $entry): void
    {
        if ($entry->getLoc() === '') {
            $this->fail('Entry loc cannot be empty.');
        }

        $priority = $entry->getPriority();
        if ($priority !== null && ($priority < 0.0 || $priority > 1.0)) {
            $this->fail('Entry priority must be between 0.0 and 1.0.');
        }

        $changefreq = $entry->getChangefreq();
        if ($changefreq !== null) {
            $allowed = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
            if (!in_array($changefreq, $allowed, true)) {
                $this->fail(sprintf('Invalid changefreq "%s".', $changefreq));
            }
        }

        foreach ($entry->getAlternates() as $alternate) {
            if ($alternate->getHrefLang() === '') {
                $this->fail('Alternate hreflang cannot be empty.');
            }

            if ($alternate->getHref() === '') {
                $this->fail('Alternate href cannot be empty.');
            }
        }

        foreach ($entry->getImages() as $image) {
            if ($image->getLoc() === '') {
                $this->fail('Image loc cannot be empty.');
            }
        }
    }

    private function fail(string $message): void
    {
        if ($this->strict) {
            throw new InvalidSitemapEntryException($message);
        }
    }
}
