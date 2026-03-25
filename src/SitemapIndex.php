<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

final class SitemapIndex
{
    /** @var list<SitemapFile> */
    private array $files = [];

    public function addFile(SitemapFile $file): self
    {
        $this->files[] = $file;
        return $this;
    }

    /** @return list<SitemapFile> */
    public function getFiles(): array
    {
        return $this->files;
    }
}
