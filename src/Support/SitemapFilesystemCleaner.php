<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use DirectoryIterator;
use RuntimeException;

final class SitemapFilesystemCleaner
{
    public function __construct(
        private string $folder,
    ) {}

    public function clear(): void
    {
        $iterator = new DirectoryIterator($this->folder);

        foreach ($iterator as $file) {
            if ($file->isDot() || !$file->isFile()) {
                continue;
            }

            if (preg_match('~^[^/\\\\]+-sitemap(?:-index)?\.xml(?:\.gz)?$~i', $file->getFilename()) !== 1) {
                continue;
            }

            if (!unlink($file->getPathname())) {
                throw new RuntimeException(sprintf(
                    'Unable to delete sitemap file: %s',
                    $file->getPathname()
                ));
            }
        }
    }
}