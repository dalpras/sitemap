<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Contract;

interface RendererInterface
{
    public function render(object $document): string;
}
