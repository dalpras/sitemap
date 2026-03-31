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

    public function withBaseUrl(string $baseUrl): self
    {
        return new self(
            baseUrl: rtrim($baseUrl, '/'),
            formatOutput: $this->formatOutput,
            allowAbsoluteUrls: $this->allowAbsoluteUrls,
            gzip: $this->gzip,
            maxEntriesPerFile: $this->maxEntriesPerFile,
            maxUncompressedBytesPerFile: $this->maxUncompressedBytesPerFile,
            strictValidation: $this->strictValidation,
        );
    }    
}
