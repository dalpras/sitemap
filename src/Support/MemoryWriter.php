<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DalPraS\Sitemap\Contract\OutputWriterInterface;

final class MemoryWriter implements OutputWriterInterface
{
    /** @var array<string, string> */
    private array $files = [];

    public function write(string $filename, string $content): void
    {
        $this->files[$filename] = $content;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->files;
    }
}
