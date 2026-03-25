<?php declare(strict_types=1);

namespace DalPraS\Sitemap;

final class AlternateLink
{
    public function __construct(
        private string $hreflang,
        private string $href,
    ) {}

    public function getHrefLang(): string
    {
        return $this->hreflang;
    }

    public function getHref(): string
    {
        return $this->href;
    }
}
