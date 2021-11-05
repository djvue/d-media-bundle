<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;

final class MediaStorage implements MediaStorageInterface
{
    public function __construct(
        private FilesystemOperator $mediasStorage
    ) {
    }

    /**
     * @throws FilesystemException
     */
    public function save(string $path, string $content): void
    {
        $this->mediasStorage->write($path, $content);
    }
}
