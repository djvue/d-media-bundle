<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DTO;

class MediaUpdateDTO
{
    public function __construct(
        private string $altText,
        private string $caption,
        private array $entities,
    ) {
    }

    public function getAltText(): string
    {
        return $this->altText;
    }

    public function getCaption(): string
    {
        return $this->caption;
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
