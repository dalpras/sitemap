<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Config;

final class SitemapConfig
{
    public function __construct(
        public readonly string $entryBaseUrl,
        public readonly string $sitemapBaseUrl,
        public readonly bool $formatOutput = true,
        public readonly bool $allowAbsoluteUrls = true,
        public readonly bool $gzip = false,
        public readonly int $maxEntriesPerFile = 50000,
        public readonly ?int $maxUncompressedBytesPerFile = 52428800,
        public readonly bool $strictValidation = true,
    ) {}

    public function withEntryBaseUrl(string $entryBaseUrl): self
    {
        return new self(
            entryBaseUrl: rtrim($entryBaseUrl, '/'),
            sitemapBaseUrl: $this->sitemapBaseUrl,
            formatOutput: $this->formatOutput,
            allowAbsoluteUrls: $this->allowAbsoluteUrls,
            gzip: $this->gzip,
            maxEntriesPerFile: $this->maxEntriesPerFile,
            maxUncompressedBytesPerFile: $this->maxUncompressedBytesPerFile,
            strictValidation: $this->strictValidation,
        );
    }

    public function withSitemapBaseUrl(string $sitemapBaseUrl): self
    {
        return new self(
            entryBaseUrl: $this->entryBaseUrl,
            sitemapBaseUrl: rtrim($sitemapBaseUrl, '/'),
            formatOutput: $this->formatOutput,
            allowAbsoluteUrls: $this->allowAbsoluteUrls,
            gzip: $this->gzip,
            maxEntriesPerFile: $this->maxEntriesPerFile,
            maxUncompressedBytesPerFile: $this->maxUncompressedBytesPerFile,
            strictValidation: $this->strictValidation,
        );
    }
}
