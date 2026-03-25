<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

final class ImageReference
{
    public function __construct(
        private string $loc,
        private ?string $caption = null,
        private ?string $title = null,
    ) {}

    public function getLoc(): string
    {
        return $this->loc;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }
}
