<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

use DateTimeInterface;

final class SitemapEntry
{
    /** @var list<AlternateLink> */
    private array $alternates = [];

    /** @var list<ImageReference> */
    private array $images = [];

    public function __construct(
        private string $loc,
        private ?DateTimeInterface $lastmod = null,
        private ?string $changefreq = null,
        private ?float $priority = null,
    ) {}

    public function getLoc(): string
    {
        return $this->loc;
    }

    public function getLastmod(): ?DateTimeInterface
    {
        return $this->lastmod;
    }

    public function getChangefreq(): ?string
    {
        return $this->changefreq;
    }

    public function getPriority(): ?float
    {
        return $this->priority;
    }

    /** @return list<AlternateLink> */
    public function getAlternates(): array
    {
        return $this->alternates;
    }

    /** @return list<ImageReference> */
    public function getImages(): array
    {
        return $this->images;
    }

    public function addAlternate(AlternateLink $alternate): self
    {
        $this->alternates[] = $alternate;
        return $this;
    }

    public function addImage(ImageReference $image): self
    {
        $this->images[] = $image;
        return $this;
    }
}
