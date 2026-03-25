<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

final class Sitemap
{
    /** @var list<SitemapEntry> */
    private array $entries = [];

    public function __construct(
        private string $name,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function addEntry(SitemapEntry $entry): self
    {
        $this->entries[] = $entry;
        return $this;
    }

    /** @return list<SitemapEntry> */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function count(): int
    {
        return count($this->entries);
    }
}
