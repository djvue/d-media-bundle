<?php

declare(strict_types=1);

namespace Djvue\DMediaBundle\Tests\Controller;

use Djvue\DMediaBundle\DataFixtures\EntityHasMediaFixtures;
use Djvue\DMediaBundle\DataFixtures\MediaFixtures;
use Djvue\DMediaBundle\Repository\MediaRepository;
use Djvue\DMediaBundle\Tests\BaseWebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @internal
 * @covers \Djvue\DMediaBundle\Controller\MediaController
 * @group init
 */
final class MediaControllerTest extends BaseWebTestCase
{
    protected array $fixtures = [
        MediaFixtures::class,
        EntityHasMediaFixtures::class,
    ];

    /*public function testCommand(): void
    {
        $kernel = static::createKernel();
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel);

        $command = $application->find('debug:router');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        echo $output;
    }*/

    public function testFind(): void
    {
        $id = 1;
        $data = $this->api('GET', '/media/'.$id);
        $this->assertApiSuccess();

        self::assertIsInt($data['data']['media']['id'] ?? null);
    }

    private function getFile(): UploadedFile
    {
        $file = tempnam(sys_get_temp_dir(), 'upl');
        imagepng(imagecreatetruecolor(10, 10), $file);
        return new UploadedFile(
            $file,
            'new_image.png'
        );
    }

    public function testUpload(): void
    {
        $this->client->request('POST', '/media', ['entities' => json_encode(['workspace' => [1]])], ['file' => $this->getFile()]);
        $data = $this->clientJsonData();
        $this->assertApiCreated();

        self::assertIsInt($data['data']['media']['id'] ?? null);
    }

    public function testUpdate(): void
    {
        // cant use data provider because of database reset between tests
        $data = [[
                'workspace' => [1, 2],
                'project' => [1],
            ],
            [
                'workspace' => [2, 3],
                'project' => [3],
            ]
        ];
        foreach ($data as $entities) {
            $this->updateIteration($entities);
        }
    }

    private function updateIteration($entities): void
    {
        $id = 3;
        $inputData = [
            'altText' => $this->faker->unique()->name,
            'caption' => $this->faker->unique()->name,
            'entities' => $entities,
        ];
        $data = $this->api('PUT', '/media/'.$id, $inputData);
        $this->assertApiSuccess();

        sort($inputData['entities']['workspace']);
        sort($data['data']['media']['entities']['workspace']);
        sort($inputData['entities']['project']);
        sort($data['data']['media']['entities']['project']);
        self::assertEquals($inputData['altText'], $data['data']['media']['altText']);
        self::assertEquals($inputData['caption'], $data['data']['media']['caption']);
        self::assertEquals($inputData['entities']['workspace'], $data['data']['media']['entities']['workspace']);
        self::assertEquals($inputData['entities']['project'], $data['data']['media']['entities']['project']);
    }

    public function testDelete(): void
    {
        $id = 3;
        $this->api('DELETE', '/media/'.$id);

        $item = self::$container->get(MediaRepository::class)->find($id);
        $this->assertApiSuccess();
        self::assertNull($item);
    }

    public function testGetList(): void
    {
        $data = $this->api('GET', '/media?page=1&limit=1&entities=%7B%22workspace%22:[2]%7D');
        $this->assertApiSuccess();
        self::assertIsArray($data['data']['medias']);
        self::assertNotEmpty($data['data']['medias']);
    }
}
