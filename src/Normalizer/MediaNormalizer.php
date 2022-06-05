<?php

namespace Djvue\DMediaBundle\Normalizer;

use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Service\MediaEntityService;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizer implements NormalizerInterface
{
    public function __construct(
        private MediaEntityService $mediaEntityService,
        private string $storagePublicUrl
    ) {
    }

    /**
     * @param Media $object
     *
     * @return array|string
     */
    public function normalize($object, string $format = null, array $context = []): array|string
    {
        $mediaUrl = $this->makeMediaUrl($object->getPath());

        $baseData = [
            'url' => $mediaUrl,
            'altText' => $object->getAltText() ?? '',
            'caption' => $object->getCaption() ?? '',
            'width' => $object->getWidth(),
            'height' => $object->getHeight(),
            'sizes' => [
                'card' => '',
                'preview' => '',
            ],
        ];

        if (!empty($context['groups']) && $context['groups'] === ['outer']) {
            return $baseData;
        }

        return $baseData + [
            'id' => $object->getId(),
            'type' => $object->getType(),
            'name' => $object->getName(),
            'humanSize' => $this->formatSize($object->getSize() ?? 0),
            'size' => $object->getSize(),
            'mimeType' => $object->getMimeType(),
            'entities' => $this->mediaEntityService->getEntities($object)
        ];
    }

    protected function makeMediaUrl(string $path): string
    {
        return rtrim($this->storagePublicUrl, '/').'/'.ltrim($path, '/');
    }

    #[Pure]
    public function supportsNormalization($data, string $format = null): bool
    {
        return is_object($data) && $data instanceof Media;
    }

    #[Pure]
    private function formatSize(int $sizeInBytes): string
    {
        $bytesInKb = 1024;
        $bytesInMb = 1024 ** 2;

        return match (true) {
            $sizeInBytes > $bytesInMb => sprintf('%1.2f MB', $sizeInBytes / $bytesInMb),
            $sizeInBytes > $bytesInKb => sprintf('%1.2f KB', $sizeInBytes / $bytesInKb),
            default => sprintf('%1.2f Byte', $sizeInBytes)
        };
    }
}
