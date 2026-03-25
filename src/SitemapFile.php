<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

use DateTimeInterface;

final class SitemapFile
{
    public function __construct(
        private string $filename,
        private string $content,
        private ?DateTimeInterface $lastmod = null,
    ) {}

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getLastmod(): ?DateTimeInterface
    {
        return $this->lastmod;
    }
}
