<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DalPraS\Sitemap\Contract\UrlResolverInterface;

final class BaseUrlResolver implements UrlResolverInterface
{
    private string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly bool $allowAbsoluteUrls = true,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function resolve(string $path): string
    {
        if ($this->allowAbsoluteUrls && preg_match('~^https?://~i', $path) === 1) {
            return $path;
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }
}
