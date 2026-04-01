<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Service;

final class SitemapGeneratorFactory
{
    public function __construct(
        private readonly SitemapRuntimeContextFactory $contextFactory,
    ) {}

    public function create(?string $baseUrl = null, ?string $folder = null): SitemapGenerator
    {
        return $this->contextFactory->create($baseUrl, $folder)->generator;
    }
}