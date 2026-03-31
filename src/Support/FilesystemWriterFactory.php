<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

final class FilesystemWriterFactory
{
    public function __construct(
        private readonly FilesystemWriter $writer,
    ) {}

    public function create(?string $folder = null): FilesystemWriter
    {
        return $folder !== null
            ? $this->writer->withFolder($folder)
            : $this->writer;
    }
}