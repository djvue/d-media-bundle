<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\DTO\MediaUpdateDTO;
use Djvue\DMediaBundle\DTO\MediaUploadDTO;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Exceptions\MediaNotFoundException;

interface MediaServiceInterface
{
    /**
     * @throws MediaNotFoundException
     */
    public function update(Media $media, MediaUpdateDTO $dto): Media;

    public function find(int $id): Media;

    public function upload(MediaUploadDTO $dto): Media;

    public function remove(Media $media): void;

    /**
     * @return Media[]
     */
    public function getList(MediaGetListParametersDTO $dto): array;

    /**
     * @return array<Media::TYPE_*, int>
     */
    public function getListTotals(MediaGetListParametersDTO $dto): array;
}
