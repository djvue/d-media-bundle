<?php

namespace Djvue\DMediaBundle\Controller;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\DTO\MediaUpdateDTO;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Exceptions\MediaNotFoundException;
use Djvue\DMediaBundle\Normalizer\MediaNormalizer;
use Djvue\DMediaBundle\Service\MediaService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

final class MediaController
{
    public function __construct(
        private MediaService $mediaService,
        private MediaNormalizer $mediaNormalizer,
        private SerializerInterface $serializer,
    ) {
    }

    private function parseRequestJson(Request $request): void
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $request->request->replace(is_array($data) ? $data : []);
    }

    private function json($data, int $status = 200, array $headers = [], SerializerInterface $serializer = null, array $context = []): JsonResponse
    {
        $serializer ??= $this->serializer;
        $json = $serializer->serialize($data, 'json', array_merge([
            'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS,
        ], $context));

        return new JsonResponse($json, $status, $headers, true);
    }

    private function getResponse(
        bool $success,
        int $code,
        string $message,
        array $data = [],
        array $headers = [],
        SerializerInterface $serializer = null,
        array $context = []
    ): JsonResponse
    {
        return $this->json([
          'success' => $success,
          'message' => $message,
          'data' => $data,
        ], $code, $headers, $serializer, $context);
    }

    private function getMediaNotFoundResponse(string $message = 'media not found'): JsonResponse
    {
        return $this->getResponse(false, 404, $message);
    }

    private function getBadInputResponse(string $message = 'bad input'): JsonResponse
    {
        return $this->getResponse(false, 400, $message);
    }

    private function getOkResponse(array $data = []): JsonResponse
    {
        return $this->getResponse(true, JsonResponse::HTTP_OK, 'ok', $data);
    }

    private function getCreatedResponse(array $data = []): JsonResponse
    {
        return $this->getResponse(true, JsonResponse::HTTP_CREATED, 'created', $data);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        try {
            $this->parseRequestJson($request);
        } catch (\JsonException $e) {
            return $this->getBadInputResponse();
        }
        $dto = new MediaUpdateDTO(
            $request->get('altText', ''),
            $request->get('caption', ''),
            $request->get('entities', [])
        );
        try {
            $media = $this->mediaService->update($id, $dto);
        } catch (MediaNotFoundException $exception) {
            return $this->getMediaNotFoundResponse($exception->getMessage());
        }

        return $this->getOkResponse(['media' => $media]);
    }

    public function get(int $id): JsonResponse
    {
        try {
            $media = $this->mediaService->get($id);
        } catch (MediaNotFoundException $exception) {
            return $this->getMediaNotFoundResponse($exception->getMessage());
        }

        return $this->getOkResponse(['media' => $media]);
    }

    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        $media = $this->mediaService->upload($file);

        return $this->getCreatedResponse(['media' => $media]);
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $this->mediaService->remove($id);
        } catch (MediaNotFoundException $exception) {
            return $this->getMediaNotFoundResponse($exception->getMessage());
        }

        return $this->getOkResponse();
    }

    public function getList(Request $request): JsonResponse
    {
        $entities = $request->get('entities', '');
        try {
            $entities = json_decode($entities, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            $entities = [];
        }
        if (!is_array($entities)) {
            $entities = [];
        }
        $dto = new MediaGetListParametersDTO(
            $request->get('type', Media::TYPE_IMAGES),
            $request->get('search', ''),
            $entities,
            $request->get('page', 1),
            $request->get('limit')
        );
        $items = $this->mediaService->getList($dto);

        $data = [
            'medias' => $items,
            'totals' => null,
        ];
        if ($request->get('withTotals') !== null) {
            $data['totals'] = $this->mediaService->getListTotals($dto);
        }

        return $this->getOkResponse($data);
    }
}
