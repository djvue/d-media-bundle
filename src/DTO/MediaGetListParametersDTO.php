<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\DTO;

class MediaGetListParametersDTO
{
    public function __construct(
        private string $type,
        private string $search,
        private array $entities,
        private int $page,
        private ?int $limit,
    )
    {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getSearch(): ?string
    {
        return $this->search;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getEntities(): array
    {
        return $this->entities;
    }
}
