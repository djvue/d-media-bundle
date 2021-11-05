<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

interface MediaStorageInterface
{
    public function save(string $path, string $content): void;
}
