<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Config;

final class SitemapConfig
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly bool $formatOutput = true,
        public readonly bool $allowAbsoluteUrls = true,
        public readonly bool $gzip = false,
        public readonly int $maxEntriesPerFile = 50000,
        public readonly ?int $maxUncompressedBytesPerFile = 52428800,
        public readonly bool $strictValidation = true,
    ) {}
}
