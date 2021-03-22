<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DTO;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class MediaUploadDTO
{
    public function __construct(
        private UploadedFile $file,
        private array $entities,
    ) {
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    /**
     * @return array<string, int[]>
     */
    public function getEntities(): array
    {
        $entities = [];
        foreach ($this->entities as $entityType => $entityIds) {
            if (!is_string($entityType)) {
                continue;
            }
            if (!is_array($entityIds)) {
                continue;
            }
            $entities[$entityType] = array_filter($entityIds, static fn ($el) => is_int($el) && $el > 0);
        }
        return $entities;
    }
}
