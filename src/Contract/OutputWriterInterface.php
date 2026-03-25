<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Contract;

interface OutputWriterInterface
{
    public function write(string $filename, string $content): void;
}
