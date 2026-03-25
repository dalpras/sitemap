<?php declare(strict_types=1);

namespace DalPraS\Sitemap\Support;

use RuntimeException;
use DalPraS\Sitemap\Contract\OutputWriterInterface;
use DalPraS\Sitemap\Exception\WriteException;

final class FilesystemWriter implements OutputWriterInterface
{
    public function __construct(
        private string $folder,
    ) {
        if (!is_dir($this->folder) && !mkdir($this->folder, 0755, true) && !is_dir($this->folder)) {
            throw new RuntimeException(sprintf('Unable to create output folder: %s', $this->folder));
        }
    }

    public function write(string $filename, string $content): void
    {
        $pathname = rtrim($this->folder, '/\\') . DIRECTORY_SEPARATOR . $filename;
        $result = file_put_contents($pathname, $content);

        if ($result === false) {
            throw new WriteException(sprintf('Unable to write file: %s', $pathname));
        }
    }
}
