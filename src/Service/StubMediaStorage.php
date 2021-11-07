<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

final class StubMediaStorage implements MediaStorageInterface
{
    public function save(string $path, string $content): void
    {
        // do nothing
    }
}
