<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Contract;

interface UrlResolverInterface
{
    public function resolve(string $path): string;
}
