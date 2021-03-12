<?php

namespace Djvue\DMediaBundle\Entity;

use Djvue\DMediaBundle\Repository\MediaRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MediaRepository::class)
 */
class Media
{
    public const TYPE_IMAGES = 'images';
    public const TYPE_FILES = 'files';
    public const TYPES = [self::TYPE_IMAGES, self::TYPE_FILES];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $path;

    /**
     * @ORM\Column(type="string", length=16)
     * @property self::TYPE_*
     */
    private string $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $altText;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $caption;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $size;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $width;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private ?int $height;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    private ?string $mimeType;

    /**
     * @ORM\OneToMany(targetEntity=EntityHasMedia::class, mappedBy="media", orphanRemoval=true)
     */
    private Collection $entities;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return self::TYPE_*
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string
     * @psalm-param self::TYPE_* $type
     * @return Media
     */
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAltText(): ?string
    {
        return $this->altText ?? '';
    }

    public function setAltText(?string $altText): self
    {
        $this->altText = $altText;

        return $this;
    }

    public function getCaption(): ?string
    {
        return $this->caption ?? '';
    }

    public function setCaption(?string $caption): self
    {
        $this->caption = $caption;

        return $this;
    }

    public function getSize(): ?int
    {
        return $this->size;
    }

    public function setSize(?int $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
