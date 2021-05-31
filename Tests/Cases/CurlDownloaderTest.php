<?php

namespace Prokl\InstagramParserRapidApiBundle\Tests\Cases;

use Mmo\Faker\PicsumProvider;
use Prokl\InstagramParserRapidApiBundle\Services\Transport\CurlDownloader;
use Prokl\TestingTools\Base\BaseTestCase;
use RuntimeException;

/**
 * Class CurlDownloaderTest
 * @package Prokl\InstagramParserRapidApiBundle\Tests\Cases
 *
 * @since 31.05.2021
 */
class CurlDownloaderTest extends BaseTestCase
{
    /**
     * @var CurlDownloader $obTestObject
     */
    protected $obTestObject;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->faker->addProvider(new PicsumProvider($this->faker));

        $this->obTestObject = new CurlDownloader($_SERVER['DOCUMENT_ROOT']);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @unlink($_SERVER['DOCUMENT_ROOT'] . '/Tests/Fixtures/result.jpg');
    }

    /**
     * download(). 404 - файл не найден.
     *
     * @return void
     */
    public function testDownloadNotFound() : void
    {
        $path = 'http://' . $_SERVER['SERVER_NAME'] . '/Tests/Fixtures/images/test.jpg';

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Get Request Error: error by http code 404 in context: ' . $path
        );
        $this->obTestObject->download(
            $path,
            '/result.jpg'
        );
    }

    /**
     * download(). Write error.
     *
     * @return void
     */
    public function testDownloadWriteError() : void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $imageUrl = $this->faker->picsumStaticRandomUrl(200, 200);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'File error: ' . $_SERVER['DOCUMENT_ROOT'] . '/fake/result.jpg'
        );
        $this->obTestObject->download(
            $imageUrl,
            '/fake/result.jpg'
        );
    }

    /**
     * download().
     *
     * @return void
     */
    public function testDownload() : void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $imageUrl = $this->faker->picsumStaticRandomUrl(200, 200);
        $testImagePath = $_SERVER['DOCUMENT_ROOT'] . '/Tests/Fixtures/result.jpg';

        $this->obTestObject->download(
            $imageUrl,
            '/Tests/Fixtures/result.jpg'
        );

        $existFile = file_exists($testImagePath);

        $this->assertTrue($existFile);

        $content = file_get_contents($testImagePath);
        $this->assertStringContainsString(
            'JFIF',
            $content,
            'Скачивание файла не задалось. '
        );
    }
}