<?php

namespace Djvue\DMediaBundle\Service;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\DTO\MediaUpdateDTO;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Djvue\DMediaBundle\Exceptions\MediaNotFoundException;
use JetBrains\PhpStorm\ArrayShape;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private MediaRepository $repository,
        private SluggerInterface $slugger,
        private FilesystemOperator $mediasStorage,
        private ParameterBagInterface $parameterBag,
        private MediaEntityService $mediaEntityService,
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
     * @param UploadedFile $file
     * @return Media
     * @throws FilesystemException
     */
    public function upload(UploadedFile $file): Media
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = strtolower($this->slugger->slug($originalFilename));
        $extension = $file->guessExtension() ?? 'unknown';
        $newFilename = $safeFilename.'-'.bin2hex(random_bytes(10)).'.'.$extension;
        $path = trim($this->parameterBag->get('d_media.storage.directory'), '/').'/'.$newFilename;
        $this->mediasStorage->write($path, $file->getContent());
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

        return $media;
    }

    private function getMediaTypeByExtension(string $extension): string
    {
        $imageExtensions = $this->parameterBag->get('d_media.library.image_extensions');
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
