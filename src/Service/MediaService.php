<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Service;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\DTO\MediaUpdateDTO;
use Djvue\DMediaBundle\DTO\MediaUploadDTO;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Exceptions\MediaNotFoundException;
use Djvue\DMediaBundle\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaService implements MediaServiceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaRepository $repository,
        private SluggerInterface $slugger,
        private MediaStorageInterface $storage,
        private MediaEntityServiceInterface $mediaEntityService,
        private string $storageDirectory,
        private string $libraryImageExtensions,
    ) {
    }

    /**
     * @param Media $media
     * @param MediaUpdateDTO $dto
     * @return Media
     * @throws MediaNotFoundException
     */
    public function update(Media $media, MediaUpdateDTO $dto): Media
    {
        $media->setAltText($dto->getAltText());
        $media->setCaption($dto->getCaption());
        $entities = $dto->getEntities();
        foreach ($entities as $entityType => $entityIds) {
            $this->mediaEntityService->syncMediaEntities($media, $entityType, $entityIds);
        }
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        return $media;
    }

    public function find(int $id): Media
    {
        $media = $this->repository->find($id);
        if (null === $media) {
            throw new MediaNotFoundException(sprintf('Media with id %d not found', $id));
        }

        return $media;
    }

    /**
     * @param MediaUploadDTO $dto
     * @return Media
     */
    public function upload(MediaUploadDTO $dto): Media
    {
        $file = $dto->getFile();
        $entities = $dto->getEntities();
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = strtolower((string) $this->slugger->slug($originalFilename));
        $extension = $file->guessExtension() ?? 'unknown';
        $newFilename = $safeFilename.'-'.bin2hex(random_bytes(10)).'.'.$extension;
        $path = trim($this->storageDirectory, '/').'/'.$newFilename;
        $this->storage->save($path, $file->getContent());
        $name = mb_strcut($originalFilename, 0, 255);
        $type = $this->getMediaTypeByExtension($extension);
        $media = new Media();
        $media->setName($name);
        $media->setPath($path);
        $media->setType($type);
        $media->setSize($file->getSize());
        [$width, $height] = getimagesize($file->getPathname());
        $media->setWidth($width);
        $media->setHeight($height);
        $mimeType = $file->getMimeType();
        $media->setMimeType($mimeType);
        $this->entityManager->persist($media);
        $this->entityManager->flush();
        foreach ($entities as $entityType => $entityIds) {
            $this->mediaEntityService->syncMediaEntities($media, $entityType, $entityIds);
        }
        $this->entityManager->flush();

        return $media;
    }

    private function getMediaTypeByExtension(string $extension): string
    {
        $imageExtensions = $this->libraryImageExtensions;
        $imageExtensions = explode(',', $imageExtensions);
        $imageExtensions = array_map(static fn($ext) => trim($ext), $imageExtensions);

        return in_array($extension, $imageExtensions, true) ? Media::TYPE_IMAGES : Media::TYPE_FILES;
    }

    /**
     * @param Media $media
     */
    public function remove(Media $media): void
    {;
        $this->entityManager->remove($media);
        $this->entityManager->flush();
    }

    /**
     * @param MediaGetListParametersDTO $dto
     * @return Media[]
     */
    public function getList(MediaGetListParametersDTO $dto): array
    {
        return $this->repository->findByParameters($dto);
    }

    /**
     * @param MediaGetListParametersDTO $dto
     * @return array<Media::TYPE_*, int>
     */
    public function getListTotals(MediaGetListParametersDTO $dto): array
    {
        return array_combine(
            Media::TYPES,
            array_map(function($type) use ($dto) {
                $typeCountDto = new MediaGetListParametersDTO($type, $dto->getSearch(), $dto->getEntities(), 0, null);
                return $this->repository->getCountByParameters($typeCountDto);
            }, Media::TYPES)
        );
    }
}
