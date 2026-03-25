<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DalPraS\Sitemap\Config\SitemapConfig;
use DalPraS\Sitemap\Contract\UrlResolverInterface;

final class BaseUrlResolver implements UrlResolverInterface
{
    private string $baseUrl;

    public function __construct(
        private SitemapConfig $config,
    ) {
        $this->baseUrl = rtrim($config->baseUrl, '/');
    }

    public function resolve(string $path): string
    {
        if ($this->config->allowAbsoluteUrls && preg_match('~^https?://~i', $path) === 1) {
            return $path;
        }

        return $this->baseUrl . '/' . ltrim($path, '/');
    }
}
