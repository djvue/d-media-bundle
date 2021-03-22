<?php

namespace Djvue\DMediaBundle\Controller;

use Djvue\DMediaBundle\DTO\MediaGetListParametersDTO;
use Djvue\DMediaBundle\DTO\MediaUpdateDTO;
use Djvue\DMediaBundle\DTO\MediaUploadDTO;
use Djvue\DMediaBundle\Entity\Media;
use Djvue\DMediaBundle\Exceptions\MediaNotFoundException;
use Djvue\DMediaBundle\Normalizer\MediaNormalizer;
use Djvue\DMediaBundle\Security\MediaListGateInterface;
use Djvue\DMediaBundle\Security\MediaPermissions;
use Djvue\DMediaBundle\Service\MediaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class MediaController extends AbstractController
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

    private function getResponse(
        bool $success,
        int $code,
        string $message,
        array $data = [],
        array $headers = [],
        array $context = []
    ): JsonResponse
    {
        return $this->json([
          'success' => $success,
          'message' => $message,
          'data' => $data,
        ], $code, $headers, $context);
    }

    private function getMediaNotFoundResponse(string $message = 'media not found'): JsonResponse
    {
        return $this->getResponse(false, 404, $message);
    }

    private function getBadInputResponse(string $message = 'bad input'): JsonResponse
    {
        return $this->getResponse(false, 400, $message);
    }

    private function getForbiddenResponse(string $message = 'Access denied'): JsonResponse
    {
        return $this->getResponse(false, JsonResponse::HTTP_FORBIDDEN, $message);
    }

    private function getOkResponse(array $data = []): JsonResponse
    {
        return $this->getResponse(true, JsonResponse::HTTP_OK, 'ok', $data);
    }

    private function getCreatedResponse(array $data = []): JsonResponse
    {
        return $this->getResponse(true, JsonResponse::HTTP_CREATED, 'created', $data);
    }

    private function isSecurityEnabled(): bool
    {
        return $this->container->has('security.authorization_checker');
    }

    protected function isGranted($attribute, $subject = null): bool
    {
        if (!$this->container->has('security.authorization_checker')) {
            return true;
        }

        return $this->container->get('security.authorization_checker')->isGranted($attribute, $subject);
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
            $media = $this->mediaService->find($id);
            $this->denyAccessUnlessGranted(MediaPermissions::EDIT, $media);
            $media = $this->mediaService->update($media, $dto);
        } catch (AccessDeniedException $exception) {
            return $this->getForbiddenResponse($exception->getMessage());
        } catch (MediaNotFoundException $exception) {
            return $this->getMediaNotFoundResponse($exception->getMessage());
        }

        return $this->getOkResponse(['media' => $media]);
    }

    public function find(int $id): JsonResponse
    {
        try {
            $media = $this->mediaService->find($id);
            $this->denyAccessUnlessGranted(MediaPermissions::VIEW, $media);
        } catch (AccessDeniedException $exception) {
            return $this->getForbiddenResponse($exception->getMessage());
        } catch (MediaNotFoundException $exception) {
            return $this->getMediaNotFoundResponse($exception->getMessage());
        }

        return $this->getOkResponse(['media' => $media]);
    }

    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');
        if ($file === null) {
            return $this->getBadInputResponse('file not found');
        }
        $dto = new MediaUploadDTO(
            $request->files->get('file'),
            json_decode($request->request->get('entities', ''), true) ?? [],
        );
        try {
            $this->denyAccessUnlessGranted(MediaPermissions::UPLOAD, $dto);
            $media = $this->mediaService->upload($dto);
        } catch (AccessDeniedException $exception) {
            return $this->getForbiddenResponse($exception->getMessage());
        }

        return $this->getCreatedResponse(['media' => $media]);
    }

    public function delete(int $id): JsonResponse
    {
        try {
            $media = $this->mediaService->find($id);
            $this->denyAccessUnlessGranted(MediaPermissions::DELETE, $media);
            $this->mediaService->remove($media);
        } catch (AccessDeniedException $exception) {
            return $this->getForbiddenResponse($exception->getMessage());
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
        try {
            $this->denyAccessUnlessGranted(MediaPermissions::GET_LIST, $dto);
        } catch (AccessDeniedException $exception) {
            return $this->getForbiddenResponse($exception->getMessage());
        }
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
