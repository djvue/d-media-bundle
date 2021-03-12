<?php

namespace Djvue\DMediaBundle\Tests;

use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Faker\Generator;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @psalm-suppress MissingDependency */
class BaseWebTestCase extends WebTestCase
{
    use FixturesTrait;

    /**
     * @property class-string[]
     */
    protected array $fixtures = [];

    protected KernelBrowser $client;

    protected EntityManager $entityManager;

    protected Generator $faker;

    protected array $apiData;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->disableReboot();

        $this->entityManager = self::$container->get('doctrine')->getManager();

        $this->loadFixtures($this->fixtures);

        $this->faker = Factory::create();

        parent::setUp();
    }

    protected function api(string $method, string $url, array $bodyJson = null): array
    {
        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'CONTENT_TYPE' => 'application/json',
        ];
        $this->client->setServerParameter('HTTP_X_REQUESTED_WITH', 'XMLHttpRequest');

        $this->client->request($method, $url, [], [], $headers, json_encode($bodyJson));

        return $this->clientJsonData();
    }

    protected function clientJsonData(): array
    {
        try {
            $this->apiData = json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            self::throwException(
                new \JsonException('Not json response '.substr($this->client->getResponse()->getContent(), 0, 500))
            );
        }

        return $this->apiData;
    }

    protected function assertApiSuccess(): void
    {
        self::assertResponseStatusCodeSame(JsonResponse::HTTP_OK);
        self::assertTrue($this->apiData['success'] ?? null);
    }

    protected function assertApiCreated(): void
    {
        self::assertResponseStatusCodeSame(JsonResponse::HTTP_CREATED);
        self::assertTrue($this->apiData['success'] ?? null);
    }

    protected function assertApiForbidden(): void
    {
        self::assertResponseStatusCodeSame(JsonResponse::HTTP_FORBIDDEN);
    }
}
