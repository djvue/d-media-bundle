<?php

namespace Djvue\DMediaBundle\Entity;

use Djvue\DMediaBundle\Repository\EntityHasMediaRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=EntityHasMediaRepository::class)
 * @ORM\Table(name="entity_has_media", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="media_id_entity_id_entity_type_propert_name_idx", columns={
 *         "media_id",
 *         "entity_id",
 *         "entity_type",
 *         "property_name"
 *     })
 * })
 */
class EntityHasMedia
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Media::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private Media $media;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private string $propertyName;

    /**
     * @ORM\Column(type="integer", options={"default": 0})
     */
    private int $listOrder;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private string $entityType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private int $entityId;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMedia(): Media
    {
        return $this->media;
    }

    public function setMedia(Media $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function setPropertyName(string $propertyName): self
    {
        $this->propertyName = $propertyName;

        return $this;
    }

    public function getListOrder(): int
    {
        return $this->listOrder;
    }

    public function setListOrder(int $listOrder): self
    {
        $this->listOrder = $listOrder;

        return $this;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): self
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }
}
